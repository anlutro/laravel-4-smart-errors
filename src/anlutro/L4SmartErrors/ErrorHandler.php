<?php
/**
 * Laravel 4 Smart Errors
 *
 * @author    Andreas Lutro <anlutro@gmail.com>
 * @license   http://opensource.org/licenses/MIT
 * @package   l4-smart-errors
 */

namespace anlutro\L4SmartErrors;

use Illuminate\Support\Facades\Response;
use Illuminate\Foundation\Application;

/**
 * The class that handles the errors. Obviously
 */
class ErrorHandler
{
	/**
	 * @var \Illuminate\Foundation\Application
	 */
	protected $app;

	/**
	 * @param \Illuminate\Foundation\Application $app
	 */
	public function __construct(Application $app)
	{
		$this->app = $app;
	}

	/**
	 * Handle an uncaught exception. Returns a view if config.app.debug == false,
	 * otherwise returns void to let the default L4 error handler do its job.
	 *
	 * @param  Exception $exception
	 * @param  integer   $code
	 *
	 * @return \Illuminate\Http\Response|void
	 */
	public function handleException($exception, $code = null)
	{
		$logstr = "Uncaught Exception (handled by L4SmartErrors)\n";

		$infoPresenter = $this->makeInfoPresenter();
		$logstr .= $infoPresenter->renderCompact();

		$input = $this->app['request']->all();
		if (!empty($input)) {
			$inputStr = with(new InputPresenter($input))->renderCompact();
			$logstr .= "\nInput: " . $inputStr;
		}

		$logstr .= "\n" . $exception;

		$this->app['log']->error($logstr);

		$email = $this->app['config']->get('smarterror::dev-email');


		// if debug is false and dev-email is set, send the mail
		if ($this->app['config']->get('app.debug') === false && $email) {
			if ($this->app['config']->get('smarterror::force-email') !== false) {
				$this->app['config']->set('mail.pretend', false);
			}

			$ePresenter = new ExceptionPresenter($exception);
			if ($this->app['config']->get('smarterror::expand-stack-trace')) {
				$ePresenter->setDescriptive(true);
			}

			$input = empty($input) ? false : new InputPresenter($input);

			if ($this->app['config']->get('smarterror::include-query-log')) {
				$queryLog = $this->app['db']->getQueryLog();
				$queryLog = new QueryLogPresenter($queryLog);
			} else {
				$queryLog = false;
			}

			$mailData = array(
				'info'      => $infoPresenter,
				'exception' => $ePresenter,
				'input'     => $input,
				'queryLog'  => $queryLog,
			);

			$env = $this->app->environment();

			$subject = "[$env] Error report - uncaught exception - " . $this->app['request']->root() ?: $this->app['config']->get('app.url');
			$htmlView = $this->app['config']->get('smarterror::error-email-view') ?: 'smarterror::error-email';
			$plainView = $this->app['config']->get('smarterror::error-email-view-plain') ?: 'smarterror::error-email-plain';

			$this->app['mailer']->send(array($htmlView, $plainView), $mailData, function($msg) use($email, $subject) {
				$msg->to($email)->subject($subject);
			});
		}

		// if debug is false, show the friendly error message
		if ($this->app['config']->get('app.debug') === false) {
			if ($this->requestIsJson()) {
				return Response::json(['errors' => [Lang::get('smarterror::genericErrorTitle')]], 500);
			} else {
				$view = $this->app['config']->get('smarterror::error-view') ?: 'smarterror::generic';
				$viewData = array(
					'referer' => $this->app['request']->header('referer'),
				);
				return Response::view($view, $viewData, 500);
			}
		}

		// if debug is true, do nothing and the default exception whoops page is shown
	}

	/**
	 * Handle an alert-level logging event.
	 *
	 * @param  string $message
	 * @param  array  $context
	 *
	 * @return void
	 */
	public function handleAlert($message, $context)
	{
		$email = $this->app['config']->get('smarterror::dev-email');

		if ($this->app['config']->get('app.debug') !== false || empty($email)) {
			return;
		}

		if ($this->app['config']->get('smarterror::force-email') !== false) {
			$this->app['config']->set('mail.pretend', false);
		}

		$mailData = array(
			'logmsg'  => $message,
			'context' => new LogContextPresenter($context),
			'info'    => $this->makeInfoPresenter(),
		);

		$subject = 'Alert logged - ' . $this->app['request']->root();
		$htmlView = $this->app['config']->get('smarterror::alert-email-view') ?: 'smarterror::alert-email';
		$plainView = $this->app['config']->get('smarterror::alert-email-view-plain') ?: 'smarterror::alert-email-plain';

		$this->app['mailer']->send(array($htmlView, $plainView), $mailData, function($msg) use($email, $subject) {
			$msg->to($email)->subject($subject);
		});
	}

	/**
	 * Handle a 404 error.
	 *
	 * @param  Exception $exception
	 *
	 * @return \Illuminate\Http\Response|void
	 */
	public function handleMissing($exception)
	{
		$url = $this->app['request']->fullUrl();
		$referer = $this->app['request']->header('referer') ?: 'none';

		$this->app['log']->warning("404 for URL $url -- Referer: $referer");

		if ($this->app['config']->get('app.debug') === false) {
			if ($this->requestIsJson()) {
				$msg = $this->app['translator']->get('smarterror::missingTitle');
				return Response::json(['errors' => [$msg]], 404);
			} else {
				$view = $this->app['config']->get('smarterror::missing-view') ?: 'smarterror::missing';
				$viewData = array(
					'referer' => $this->app['request']->header('referer'),
				);
				return Response::view($view, $viewData, 404);
			}
		}
	}

	/**
	 * Make an application information presenter object.
	 *
	 * @return \anlutro\L4SmartErrors\AppInfoPresenter
	 */
	protected function makeInfoPresenter()
	{
		$console = $this->app->runningInConsole();

		if ($console) {
			$data = array(
				'hostname' => gethostname(),
			);
		} else {
			list($routeAction, $routeName) = $this->findRouteNames();
			$data = array(
				'url' => $this->app['request']->fullUrl(),
				'method' => $this->app['request']->getMethod(),
				'route-name' => $routeName,
				'route-action' => $routeAction,
				'client' => $this->app['request']->getClientIp(),
			);
		}

		$data['environment'] = $this->app->environment();

		$timeFormat = $this->app['config']->get('smarterror::date-format') ?: 'Y-m-d H:i:s';
		$data['time'] = date($timeFormat);

		$presenter = new AppInfoPresenter($console, $data);

		return $presenter;
	}

	/**
	 * Get the action or name of the current route.
	 *
	 * @return array
	 */
	protected function findRouteNames()
	{
		$route = $this->app['router']->current();

		if (!$route) {
			return array(null, null);
		} else {
			return array($route->getActionName(), $route->getName());
		}
	}

	/**
	 * Determine whether a JSON response should be returned.
	 *
	 * @return bool
	 */
	protected function requestIsJson()
	{
		$request = $this->app['request'];
		return $request->wantsJson() || $request->isJson() || $request->ajax();
	}
}
