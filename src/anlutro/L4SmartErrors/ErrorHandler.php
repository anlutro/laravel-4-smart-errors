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
use Symfony\Component\Debug\Exception\FatalErrorException;

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
	 * @var array
	 */
	protected $handledExceptions = [];

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
	 * @param  \Exception $exception
	 * @param  integer   $code
	 *
	 * @return \Illuminate\Http\Response|void
	 */
	public function handleException($exception, $code = null)
	{
		if (in_array($exception, $this->handledExceptions)) return;
		$this->handledExceptions[] = $exception;

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

		if ($email && $this->shouldSendEmail($exception)) {
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

			$exceptionName = get_class($exception);
			$exceptionName = substr($exceptionName, strrpos($exceptionName, '\\'));
			$subject = "[$env] $exceptionName - ";
			$subject .= $this->app['request']->root() ?: $this->app['config']->get('app.url');
			$htmlView = $this->app['config']->get('smarterror::error-email-view') ?: 'smarterror::error-email';
			$plainView = $this->app['config']->get('smarterror::error-email-view-plain') ?: 'smarterror::error-email-plain';

			$callback = function($msg) use($email, $subject) {
				$msg->to($email)->subject($subject);
			};

			$this->app['mailer']->send(array($htmlView, $plainView), $mailData, $callback);
		}

		// the default laravel console error handler really sucks - override it
		if ($this->shouldReturnConsoleResponse()) {
			// if log_error is false and error_log is not set, fatal errors
			// should go to STDERR which, in the cli environment, is STDOUT
			if (
				ini_get('log_errors') === "1" &&
				!ini_get('error_log') &&
				$exception instanceof FatalErrorException
			) {
				return '';
			}

			// if the exception is not fatal, simply echo it and a newline
			return $exception . "\n";
		}

		// if debug is false, show the friendly error message
		if ($this->app['config']->get('app.debug') === false) {
			if ($this->requestIsJson()) {
				return Response::json(['errors' => [$this->app['translator']->get('smarterror::genericErrorTitle')]], 500);
			} else if ($view = $this->app['config']->get('smarterror::error-view')) {
				return Response::view($view, array(
					'referer' => $this->app['request']->header('referer'),
				), 500);
			}
		}

		// if debug is true, do nothing and the default exception whoops page is shown
	}

	/**
	 * Determine if the error handler should send an email.
	 *
	 * @param  \Exception $exception
	 *
	 * @return boolean
	 */
	protected function shouldSendEmail($exception)
	{
		// if app.debug is true, no emails should be sent
		if ($this->app['config']->get('app.debug') === true) return false;

		$files = $this->app['files'];
		$path = $this->app['config']->get('smarterror::storage-path');

		// create a basic hash of the exception. this should include the stack
		// trace and message, making it more or less a unique identifier
		$string = $exception->getMessage().$exception->getCode()
			.$exception->getTraceAsString();
		$hash = base64_encode($string);

		$data = array();

		// if the file exists, read from it and check if the hash of the current
		// exception is the same as the previous one.
		if ($files->exists($path)) {
			$data = json_decode($files->get($path), true);
			if (isset($data['previous']) && $data['previous'] == $hash) {
				return false;
			}
		}

		// if the file is writeable, write the current exception hash into it.
		if ($files->isWritable($path)) {
			$data['previous'] = $hash;
			$files->put($path, json_encode($data));
		}

		return true;
	}

	/**
	 * Determine whether a console response should be returned.
	 *
	 * @return boolean
	 */
	protected function shouldReturnConsoleResponse()
	{
		global $argv; // this fucking sucks omg

		if (isset($argv[0])) {
			foreach (['phpunit', 'codecept', 'behat', 'phpspec'] as $needle) {
				if (strpos($argv[0], $needle) !== false) return false;
			}
		}

		return $this->app->runningInConsole() && !$this->app->runningUnitTests();
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

		$callback = function($msg) use($email, $subject) {
			$msg->to($email)->subject($subject);
		};

		$this->app['mailer']->send(array($htmlView, $plainView), $mailData, $callback);
	}

	/**
	 * Handle a 404 error.
	 *
	 * @param  \Exception $exception
	 *
	 * @return \Illuminate\Http\Response|void
	 */
	public function handleMissing($exception)
	{
		if (in_array($exception, $this->handledExceptions)) return;
		$this->handledExceptions[] = $exception;

		$url = $this->app['request']->fullUrl();
		$referer = $this->app['request']->header('referer') ?: 'none';

		$this->app['log']->warning("404 for URL $url -- Referer: $referer");

		if ($this->app['config']->get('app.debug') === false) {
			if ($this->requestIsJson()) {
				$msg = $this->app['translator']->get('smarterror::missingTitle');
				return Response::json(['errors' => [$msg]], 404);
			} else if ($view = $this->app['config']->get('smarterror::missing-view')) {
				return Response::view($view, array(
					'referer' => $this->app['request']->header('referer'),
				), 404);
			}
		}
	}

	/**
	 * Handle a CSRF token mismatch exception.
	 *
	 * @param  \Illuminate\Session\TokenMismatchException $exception
	 *
	 * @return \Illuminate\Http\Response|void
	 */
	public function handleTokenMismatch($exception)
	{
		if (in_array($exception, $this->handledExceptions)) return;
		$this->handledExceptions[] = $exception;

		$logstr = "CSRF token mismatch (handled by L4SmartErrors)\n";
		$logstr .= $this->makeInfoPresenter()->renderCompact();
		$this->app['log']->warning($logstr);

		// if the request has the referer header, it's safe to redirect back to
		// the previous page with an error message. this way, no user input
		// is lost if a browser tab has been left open too long or something
		$referer = $this->app['request']->header('referer');

		// make sure the referer url is not the same as the current page url,
		// and that the method is not GET - this prevents a redirect loop
		$current = $this->app['request']->fullUrl();
		$method = $this->app['request']->getMethod();

		if ($referer && $referer != $current && $method !== 'get') {
			return $this->app['redirect']->back()->withInput()
				->withErrors($this->app['translator']->get('smarterror::error.csrfText'));
		}

		if ($this->app['config']->get('app.debug') === false) {
			if ($this->requestIsJson()) {
				return Response::json(['errors' => [$this->app['translator']->get('smarterror::error.csrfText')]], 400);
			} else if ($view = $this->app['config']->get('smarterror::csrf-view')) {
				return Response::view($view, array(
					'referer' => $this->app['request']->header('referer'),
				), 400);
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
				'url'          => $this->app['request']->fullUrl(),
				'method'       => $this->app['request']->getMethod(),
				'route-name'   => $routeName,
				'route-action' => $routeAction,
				'client'       => $this->app['request']->getClientIp(),
				'referer'      => $this->app['request']->header('referer'),
			);
		}

		$data['environment'] = $this->app->environment();

		$timeFormat = $this->app['config']->get('smarterror::date-format') ?: 'Y-m-d H:i:s e';
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
