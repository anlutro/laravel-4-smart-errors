<?php
/**
 * Laravel 4 Smart Errors
 *
 * @author    Andreas Lutro <anlutro@gmail.com>
 * @license   http://opensource.org/licenses/MIT
 * @package   l4-smart-errors
 */

namespace anlutro\L4SmartErrors\Log;

use Illuminate\Foundation\Application;
use anlutro\L4SmartErrors\Traits\ConsoleCheckingTrait;
use anlutro\L4SmartErrors\Presenters\InputPresenter;
use anlutro\L4SmartErrors\Presenters\SessionPresenter;

class ContextCollector
{
	use ConsoleCheckingTrait;

	protected $app;
	protected $input;
	protected $session;
	protected $console;
	protected $sanitizeFields = array('password');

	public function __construct(
		Application $app,
		InputPresenter $input = null,
		SessionPresenter $session = null,
		$console = null
	) {
		$this->app = $app;
		$this->input = $input;
		$this->session = $session;
		$this->console = $console === null ? $this->isConsole() : (bool) $console;
	}

	public function addSanitizedField($field)
	{
		$this->sanitizeFields[] = $field;
	}

	public function setSanitizedFields(array $fields)
	{
		$this->sanitizeFields = $fields;
	}

	public function getContext()
	{
		$context = array(
			'context'     => $this->console ? 'console' : 'web',
			'environment' => $this->app->environment(),
			'hostname'    => gethostname(),
		);

		if (!$this->console && $request = $this->app['request']) {
			$context['url']         = $request->fullUrl();
			$context['http_method'] = $request->getMethod();
			$context['referer']     = $request->header('referer') ?: 'None';
			$context['client_ip']   = $request->getClientIp();
			$context['user_agent']  = $request->header('User-Agent');
			if ($this->input) {
				$context['input'] = $this->input->getData();
			}
			if ($this->session) {
				$context['session_id'] = $this->session->getId();
				$context['session']    = $this->session->getData();
			}

			list($routeAction, $routeName) = $this->findRouteNames();
			if ($routeName)   $context['route_name'] = $routeName;
			if ($routeAction) $context['route_action'] = $routeAction;
		}

		return $context;
	}

	/**
	 * Get the action and name of the current route.
	 *
	 * @return array
	 */
	protected function findRouteNames()
	{
		/** @var \Illuminate\Routing\Route|null $route */
		$route = $this->app['router']->current();

		if (!$route) {
			return array(null, null);
		} else {
			return array($route->getActionName(), $route->getName());
		}
	}
}
