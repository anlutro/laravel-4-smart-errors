<?php
/**
 * Laravel 4 Smart Errors
 *
 * @author    Andreas Lutro <anlutro@gmail.com>
 * @license   http://opensource.org/licenses/MIT
 * @package   Laravel 4 Smart Errors
 */

namespace anlutro\L4SmartErrors;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;

/**
 * The class that handles the errors. Obviously
 */
class ErrorHandler
{
	/**
	 * Handle an uncaught exception. Returns a view if config.app.debug == false,
	 * otherwise returns void to let the default L4 error handler do its job.
	 *
	 * @param  Exception $exception
	 * @param  integer   $code
	 *
	 * @return View|void
	 */
	public function handleException($exception, $code = null)
	{
		$email = Config::get('smarterror::dev-email');
		$route = $this->findRoute();
		$url = Request::fullUrl();
		$client = Request::getClientIp();

		$logstr = "Uncaught Exception (handled by L4SmartErrors)\nURL: $url -- Route: $route -- Client: $client\n" . $exception;

		// get any input and log it
		$input = Request::all();
		if (!empty($input)) {
			$logstr .= 'Input: ' . json_encode($input);
		}

		Log::error($logstr);

		// if debug is false and dev_email is set, send the mail
		if (Config::get('app.debug') === false && $email) {
			if (Config::get('smarterror::force-email') !== false) {
				Config::set('mail.pretend', false);
			}

			$timeFormat = Config::get('smarterror::date-format', 'Y-m-d H:i:s');

			$mailData = array(
				'exception' => $exception,
				'url'       => $url,
				'route'     => $route,
				'client'    => $client,
				'input'     => $input,
				'time'      => date($timeFormat),
			);

			$subject = 'Error report - uncaught exception - ' . Request::root();
			$htmlView = Config::get('smarterror::error-email-view', 'smarterror::error-email');
			$plainView = Config::get('smarterror::error-email-view-plain', 'smarterror::error-email-plain');

			Mail::send(array($htmlView, $plainView), $mailData, function($msg) use($email, $subject) {
				$msg->to($email)->subject($subject);
			});
		}

		// if debug is false, show the friendly error message
		if (Config::get('app.debug') === false) {
			$view = Config::get('smarterror::error-view') ?: 'smarterror::generic';
			return Response::view($view, array(), 500);
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
		$url = Request::fullUrl();
		$referer = Request::header('referer');

		Log::warning("404 for URL $url -- Referer: $referer");

		if (Config::get('app.debug') === false) {
			$view = Config::get('smarterror::missing-view') ?: 'smarterror::missing';
			return Response::view($view, 404);
		}
	}

	/**
	 * Handle an alert-level logging event.
	 *
	 * @param  string $message
	 * @param  array $context
	 *
	 * @return void
	 */
	public function handleAlert($message, $context)
	{
		$email = Config::get('smarterror::dev-email');

		if (Config::get('app.debug') !== false || empty($email)) {
			return;
		}

		if (Config::get('smarterror::force-email') !== false) {
			Config::set('mail.pretend', false);
		}

		$timeFormat = Config::get('smarterror::date-format', 'Y-m-d H:i:s');

		$mailData = array(
			'logmsg'    => $message,
			'context'   => $context,
			'url'       => Request::fullUrl(),
			'route'     => $this->findRoute(),
			'time'      => date($timeFormat),
		);

		$subject = 'Alert logged - ' . Request::root();
		$htmlView = Config::get('smarterror::alert-email-view') ?: 'smarterror::alert-email';
		$plainView = Config::get('smarterror::alert-email-view-plain') ?: 'smarterror::alert-email-plain';

		Mail::send(array($htmlView, $plainView), $mailData, function($msg) use($email, $subject) {
			$msg->to($email)->subject($subject);
		});
	}

	/**
	 * Get the action or name of the current route.
	 *
	 * @return string
	 */
	protected function findRoute()
	{
		$route = Route::current();

		if (($name = $route->getName()) || ($name = $route->getActionName())) {
			return $name;
		} else {
			return 'NA (probably a console command)';
		}
	}
}
