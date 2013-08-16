<?php
/**
 * Laravel 4 Smart Errors
 *
 * @author    Andreas Lutro <anlutro@gmail.com>
 * @license   http://opensource.org/licenses/MIT
 * @package   Laravel 4 Smart Errors
 */

namespace anlutro\L4SmartErrors;

/**
 * The class that handles the errors. Obviously
 */
class ErrorHandler
{
	/**
	 * The Laravel application.
	 *
	 * @var Illuminate\Foundation\Application
	 */
	protected $app;

	/**
	 * The email to send error reports to.
	 *
	 * @var string
	 */
	protected $devEmail;
	
	/**
	 * The view to use for email error reports.
	 *
	 * @var string
	 */
	protected $emailView;

	/**
	 * The view for generic error messages.
	 *
	 * @var string
	 */
	protected $exceptionView;
	
	/**
	 * The view for 404 error messages.
	 *
	 * @var string
	 */
	protected $missingView;

	/**
	 * Construct the handler, injecting the Laravel application.
	 *
	 * @param Illuminate\Foundation\Application $app
	 */
	public function __construct($app)
	{
		$this->app = $app;

		$pkg = 'anlutro/l4-smart-errors::';

		$this->devEmail = $this->app['config']->get($pkg.'dev_email');

		// if configs are null, set some defaults
		$this->emailView = $this->app['config']->get($pkg.'email_view') ?: $pkg.'email';
		$this->exceptionView = $this->app['config']->get($pkg.'exception_view') ?: $pkg.'generic';
		$this->missingView = $this->app['config']->get($pkg.'missing_view') ?: $pkg.'missing';
	}

	/**
	 * Handle an uncaught exception. Returns a view if config.app.debug == false,
	 * otherwise returns void to let the default L4 error handler do its job.
	 *
	 * @param  Exception $exception
	 * @param  integer   $code
	 * @param  boolean   $event      Whether the exception is handled via an event
	 *
	 * @return View|void
	 */
	public function handleException($exception, $code = null, $event = false)
	{
		// get the request URL
		$url = $this->app['request']->fullUrl();

		// get the current route info
		if ($this->app['router']->currentRouteAction()) {
			$route = $this->app['router']->currentRouteAction();
		} elseif ($this->app['router']->currentRouteName()) {
			$route = $this->app['router']->currentRouteName();
		} else {
			$route = 'NA (probably a closure)';
		}

		// log the exception
		if ($event) {
			$this->app['log']->error("Exception caught by event -- URL: $url -- Route: $route");
		} else {
			$this->app['log']->error("Uncaught Exception -- URL: $url -- Route: $route");
		}
		$this->app['log']->error($exception);

		// get any input and log it
		$input = $this->app['request']->all();
		if (!empty($input)) {
			$this->app['log']->error('Input: ' . json_encode($input));
		}

		// if debug is false and dev_email is set, send the mail
		if ($this->app['config']->get('app.debug') === false && $this->devEmail) {
			// I sometimes set pretend to true in staging, but would still like an email
			$this->app['config']->set('mail.pretend', false);

			$mailData = array(
				'exception' => $exception,
				'url'       => $url,
				'route'     => $route,
				'input'     => $input,
			);

			$devEmail = $this->devEmail;
			$subject = $event ? 'Error report - event' : 'Error report - uncaught exception';

			$this->app['mailer']->send($this->emailView, $mailData, function($msg) use($devEmail, $subject) {
				$msg->to($devEmail)->subject($subject);
			});
		}

		// if debug is false, show the friendly error message
		if (!$event && $this->app['config']->get('app.debug') === false) {
			return $this->app['view']->make($this->exceptionView);
		}

		// if debug is true, do nothing and the default exception whoops page is shown
	}

	/**
	 * Handle a 404 error.
	 *
	 * @param  Exception $exception
	 *
	 * @return Response
	 */
	public function handleMissing($exception)
	{
		$url = $this->app['request']->fullUrl();
		$referer = $this->app['request']->header('referer');

		$this->app['log']->warning("404 for URL $url -- Referer: $referer");

		$content = $this->app['view']->make($this->missingView);
		return new \Illuminate\Http\Response($content, 404);
	}
}
