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
use anlutro\L4SmartErrors\Traits\ConsoleCheckingTrait;

class AppInfoGenerator
{
	use ConsoleCheckingTrait;

	protected $app;
	protected $console;
	protected $data = array();

	public function __construct(Application $app, $console = null)
	{
		$this->app = $app;

		$this->console = $console === null ? $this->isConsole() : (bool) $console;

		$this->generate();
	}

	protected function generate()
	{
		$this->addData('Environment', $this->app->environment());
		$this->addData('Hostname', gethostname());

		$timeFormat = $this->app['config']->get('smarterror::date-format') ?: 'Y-m-d H:i:s e';
		$this->addData('Time', date($timeFormat));

		if ($this->console) {
			$this->addPlainData('Console script');
		} else {
			$this->addData('URL', $this->app['request']->fullUrl());
			$this->addData('HTTP method', $this->app['request']->getMethod());
			$this->addData('Referer', $this->app['request']->header('referer') ?: 'None');
			$this->addData('Client IP', $this->app['request']->getClientIp());

			list($routeAction, $routeName) = $this->findRouteNames();
			if ($routeName)   $this->addData('Route name', $routeName);
			if ($routeAction) $this->addData('Route action', $routeAction);
		}
	}

	protected function addData($key, $value = null)
	{
		if (!$value) return;

		$this->data[$key] = $value;
	}

	protected function addPlainData($value)
	{
		if (!$value) return;

		$this->data[] = $value;
	}

	/**
	 * Get the action or name of the current route.
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

	public function getRenderableStrings()
	{
		$strings = $this->data + $this->getExtraStrings();

		return array_map(function($value, $key) {
			if (is_string($key)) {
				return ucfirst($key).': '.$value;
			} else {
				return ucfirst($value);
			}
		}, $strings, array_keys($strings));
	}

	protected function getExtraStrings()
	{
		return [];
	}

	public function renderPlain()
	{
		return implode("\n", $this->getRenderableStrings());
	}

	public function renderHtml()
	{
		return implode('<br>', $this->getRenderableStrings());
	}

	public function renderCompact()
	{
		return implode(' -- ', $this->getRenderableStrings());
	}
}
