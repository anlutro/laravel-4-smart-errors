<?php
/**
 * Laravel 4 Smart Errors
 *
 * @author    Andreas Lutro <anlutro@gmail.com>
 * @license   http://opensource.org/licenses/MIT
 * @package   l4-smart-errors
 */

namespace anlutro\L4SmartErrors\Responders;

use anlutro\L4SmartErrors\Traits\ConfigCompatibilityTrait;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\Response;

class CsrfResponder extends AbstractResponder
{
	use ConfigCompatibilityTrait;

	public function respond(TokenMismatchException $exception)
	{
		$notDebug = $this->getConfig('app.debug') === false;

		$request = $this->app['request'];

		if ($notDebug && $this->requestIsJson($request)) {
			return Response::json(array('errors' => array($this->app['translator']->get('smarterror::error.csrfText'))), 400);
		}


		// if the request has the referer header, it's safe to redirect back to
		// the previous page with an error message. this way, no user input
		// is lost if a browser tab has been left open too long or something
		$referer = $request->header('referer');

		// make sure the referer url is not the same as the current page url,
		// and that the method is not GET - this prevents a redirect loop
		$current = $request->fullUrl();
		$method = $request->getMethod();

		if ($referer && $referer != $current && $method != 'GET') {
			return $this->app['redirect']->back()->withInput()
				->withErrors($this->app['translator']->get('smarterror::error.csrfText'));
		}

		if ($notDebug && $view = $this->getConfig('smarterror::csrf-view')) {
			return Response::view($view, array(
				'referer' => $request->header('referer'),
			), 400);
		}
	}
}