<?php
/**
 * Laravel 4 Smart Errors
 *
 * @author    Andreas Lutro <anlutro@gmail.com>
 * @license   http://opensource.org/licenses/MIT
 * @package   l4-smart-errors
 */

namespace anlutro\L4SmartErrors\Traits;

trait ConsoleCheckingTrait
{
	/**
	 * Determine whether a console response should be returned.
	 *
	 * @return boolean
	 */
	protected function isConsole()
	{
		global $argv; // this fucking sucks omg

		if (isset($argv[0])) {
			foreach (array('phpunit', 'codecept', 'behat', 'phpspec') as $needle) {
				if (strpos($argv[0], $needle) !== false) return false;
			}
		}

		return $this->app->runningInConsole() && !$this->app->runningUnitTests();
	}
}
