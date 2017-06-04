<?php
/**
 * Laravel 4 Smart Errors
 *
 * @author    Andreas Lutro <anlutro@gmail.com>
 * @license   http://opensource.org/licenses/MIT
 * @package   l4-smart-errors
 */

namespace anlutro\L4SmartErrors\Responders;

use Illuminate\Support\Facades\Response;
use Symfony\Component\Debug\Exception\FatalErrorException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class ExceptionResponder extends AbstractResponder
{
	public function respond($exception)
	{
		// the default laravel console error handler really sucks - override it
		if ($this->isConsole()) {
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

		if ($exception instanceof HttpExceptionInterface) {
			$statusCode = $exception->getStatusCode();
			$headers = $exception->getHeaders();
		} else {
			$statusCode = 500;
			$headers = array();
		}

		// if debug is false, show the friendly error message
		if ($this->app['config']->get('app.debug') === false) {
			if ($this->requestIsJson()) {
				return Response::json(array('errors' => array($this->app['translator']->get('smarterror::genericErrorTitle'))), $statusCode, $headers);
			} else if ($view = $this->app['config']->get('smarterror::error-view')) {
				return Response::view($view, array(
					'referer' => $this->app['request']->header('referer'),
				), $statusCode, $headers);
			}
		}

		// if debug is true, do nothing and the default exception whoops page is shown
	}
}
