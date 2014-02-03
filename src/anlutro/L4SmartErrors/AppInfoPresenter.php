<?php
/**
 * Laravel 4 Smart Errors
 *
 * @author    Andreas Lutro <anlutro@gmail.com>
 * @license   http://opensource.org/licenses/MIT
 * @package   l4-smart-errors
 */

namespace anlutro\L4SmartErrors;

class AppInfoPresenter extends AbstractPresenter
{
	public function __construct($console, array $info)
	{
		$this->console = (bool) $console;
		$this->info = $info;
	}

	protected function info($key)
	{
		return isset($this->info[$key]) ? $this->info[$key] : null;
	}

	public function getInfoStrings()
	{
		$info = array('Time: ' . $this->info('time'));

		if ($this->console) {
			$info[] = 'Hostname: ' . $this->info('hostname');
		} else {
			$info[] = 'URL: ' . $this->info('url');
			$info[] = 'Route: ' . $this->info('route');
			$info[] = 'HTTP method: ' . $this->info('method');
			$info[] = 'Client: ' . $this->info('client');
		}

		return $info;
	}

	public function render()
	{
		return implode("\n", $this->getInfoStrings());
	}

	public function renderHtml()
	{
		return implode('<br>', $this->getInfoStrings());
	}

	public function renderCompact()
	{
		return implode(' -- ', $this->getInfoStrings());
	}

	public function __toString()
	{
		return $this->html ? $this->renderHtml() : $this->render();
	}
}
