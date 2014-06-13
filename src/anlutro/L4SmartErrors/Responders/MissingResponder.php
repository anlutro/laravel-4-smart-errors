<?php
namespace anlutro\L4SmartErrors\Responders;

use Illuminate\Foundation\Application;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Support\Facades\Response;

class MissingResponder extends AbstractResponder
{
	public function respond(NotFoundHttpException $exception)
	{
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
}
