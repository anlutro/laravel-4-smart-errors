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

class ContextCollector
{
	use ConsoleCheckingTrait;

	protected $app;
	protected $console;
	protected $sanitizeFields = ['password'];

	public function __construct(Application $app, $console = null)
	{
		$this->app = $app;
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
		$context = [
			'context'     => $this->console ? 'console' : 'web',
			'environment' => $this->app->environment(),
			'hostname'    => gethostname(),
		];

		if (!$this->console && $request = $this->app['request']) {
			$context['url']         = $request->fullUrl();
			$context['http_method'] = $request->getMethod();
			$context['input']       = $this->sanitizeInput($request->input());
			$context['referer']     = $request->header('referer') ?: 'None';
			$context['client_ip']   = $request->getClientIp();
			$context['session_id']  = $this->app['session']->getId();

			list($routeAction, $routeName) = $this->findRouteNames();
			if ($routeName)   $context['route_name'] = $routeName;
			if ($routeAction) $context['route_action'] = $routeAction;
		}

		return $context;
	}

	protected function sanitizeInput(array $input)
	{
		foreach ($input as $key => &$value) {
			if (is_array($value)) {
				$value = $this->sanitizeInput($value);
			}
			foreach ($this->sanitizeFields as $field) {
				if (strpos(strtolower($key), $field) !== false) {
					$value = 'HIDDEN';
				}
			}
		}

		return $input;
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
