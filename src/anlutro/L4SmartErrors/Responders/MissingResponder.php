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
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class MissingResponder extends AbstractResponder
{
	use ConfigCompatibilityTrait;

	public function respond(NotFoundHttpException $exception)
	{
		if ($this->getConfig('app.debug') === false) {
			if ($this->requestIsJson()) {
				$msg = $this->app['translator']->get('smarterror::missingTitle');
				return Response::json(array('errors' => array($msg)), 404);
			} else if ($view = $this->getConfig('smarterror::missing-view')) {
				return Response::view($view, array(
					'referer' => $this->app['request']->header('referer'),
				), 404);
			}
		}
	}
}
