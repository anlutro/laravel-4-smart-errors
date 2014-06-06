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
	protected $console;
	protected $info;

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
			$info[] = 'Console script';
			$info[] = 'Hostname: ' . $this->info('hostname');
		} else {
			$info[] = 'Client: ' . $this->info('client');
			$info[] = 'URL: ' . $this->info('url');
			$info[] = 'HTTP method: ' . $this->info('method');
			$info[] = 'Referer: ' . $this->info('referer');
			if ($this->info('route-action')) {
				$info[] = 'Route action: ' . $this->info('route-action');
			}
			if ($this->info('route-name')) {
				$info[] = 'Route name: ' . $this->info('route-name');
			}
		}

		return $info;
	}

	public function renderPlain()
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

	public function render()
	{
		return $this->html ? $this->renderHtml() : $this->renderLines();
	}

	public function __toString()
	{
		return $this->render();
	}
}
