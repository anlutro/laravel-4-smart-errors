<?php
/**
 * Laravel 4 Smart Errors
 *
 * @author    Andreas Lutro <anlutro@gmail.com>
 * @license   http://opensource.org/licenses/MIT
 * @package   l4-smart-errors
 */

namespace anlutro\L4SmartErrors;

use Illuminate\Foundation\Application;
use Illuminate\Support\Fluent;

class AppInfoGenerator
{
	protected $app;
	protected $data;
	protected $strings = array();

	public function __construct(Application $app)
	{
		$this->app = $app;
		$this->data = new Fluent;
		$this->generate();
	}

	protected function generate()
	{
		$this->data['hostname']    = gethostname();
		$this->data['environment'] = $this->app->environment();

		if (!$this->app->runningInConsole()) {
			$this->data['url']     = $this->app['request']->fullUrl();
			$this->data['method']  = $this->app['request']->getMethod();
			$this->data['client']  = $this->app['request']->getClientIp();
			$this->data['referer'] = $this->app['request']->header('referer');

			list($routeAction, $routeName) = $this->findRouteNames();
			$this->data['route-name']   = $routeName;
			$this->data['route-action'] = $routeAction;
		}

		$timeFormat = $this->app['config']->get('smarterror::date-format') ?: 'Y-m-d H:i:s e';
		$this->data['time'] = date($timeFormat);
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

	public function getStrings()
	{
		if (!empty($this->strings)) {
			return $this->strings;
		}

		$this->strings[] = 'Environment: ' . $this->data['environment'];
		$this->strings[] = 'Hostname: ' . $this->data['hostname'];
		$this->strings[] = 'Time: ' . $this->data['time'];

		if (!$this->app->runningInConsole()) {
			$this->strings[] = 'Console script';
		} else {
			$this->strings[] = 'Client: ' . $this->data['client'];
			$this->strings[] = 'URL: ' . $this->data['url'];
			$this->strings[] = 'HTTP method: ' . $this->data['method'];
			$this->strings[] = 'Referer: ' . $this->data['referer'];

			if ($this->data['route-action']) {
				$this->strings[] = 'Route action: ' . $this->data['route-action'];
			}

			if ($this->data['route-name']) {
				$this->strings[] = 'Route name: ' . $this->data['route-name'];
			}
		}

		return $this->strings;
	}

	public function renderPlain()
	{
		return implode("\n", $this->getStrings());
	}

	public function renderHtml()
	{
		return implode('<br>', $this->getStrings());
	}

	public function renderCompact()
	{
		return implode(' -- ', $this->getStrings());
	}
}
