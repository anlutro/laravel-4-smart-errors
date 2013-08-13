<?php
/**
 * Laravel 4 Smart Errors
 *
 * @author    Andreas Lutro <anlutro@gmail.com>
 * @license   http://opensource.org/licenses/MIT
 * @package   Laravel 4 Smart Errors
 */

namespace anlutro\L4SmartErrors;

class ErrorHandler
{
	protected $app;
	protected $devEmail;
	protected $emailView;
	protected $exceptionView;
	protected $missingView;

	public function __construct($app)
	{
		$this->app = $app;

		$pkg = 'anlutro/l4-smart-errors::';

		$this->devEmail = $this->app['config']->get($pkg.'dev_email');
		$this->emailView = $this->app['config']->get($pkg.'email_view') ?: $pkg.'email';
		$this->exceptionView = $this->app['config']->get($pkg.'exception_view') ?: $pkg.'generic';
		$this->missingView = $this->app['config']->get($pkg.'missing_view') ?: $pkg.'missing';
	}

	public function handleException($exception, $code)
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
		$this->app['log']->error("Uncaught Exception -- URL: $url -- Route: $route");
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

			$this->app['mailer']->send($this->emailView, $mailData, function($msg) use($code) {
				$msg->to($this->devEmail)->subject('Error report');
			});
		}

		// if debug is false, show the friendly error message
		if ($this->app['config']->get('app.debug') === false) {
			return $this->app['view']->make($this->exceptionView);
		}

		// if debug is true, do nothing and the default exception whoops page is shown
	}

	public function handleMissing($exception)
	{
		$url = $this->app['request']->fullUrl();
		$referer = $this->app['request']->header('referer');

		$this->app['log']->warning("404 for URL $url -- Referer: $referer");

		// dd($this->missingView);
		$content = $this->app['view']->make($this->missingView);
		return new \Illuminate\Http\Response($content, 404);
	}
}
