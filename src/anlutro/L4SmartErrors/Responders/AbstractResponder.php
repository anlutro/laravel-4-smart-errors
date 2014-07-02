<?php
/**
 * Laravel 4 Smart Errors
 *
 * @author    Andreas Lutro <anlutro@gmail.com>
 * @license   http://opensource.org/licenses/MIT
 * @package   l4-smart-errors
 */

namespace anlutro\L4SmartErrors\Responders;

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

abstract class AbstractResponder
{
	protected $app;

	public function __construct(Application $app)
	{
		$this->app = $app;
	}

	/**
	 * Determine whether a console response should be returned.
	 *
	 * @return boolean
	 */
	protected function shouldReturnConsoleResponse()
	{
		global $argv; // this fucking sucks omg

		if (isset($argv[0])) {
			foreach (array('phpunit', 'codecept', 'behat', 'phpspec') as $needle) {
				if (strpos($argv[0], $needle) !== false) return false;
			}
		}

		return $this->app->runningInConsole() && !$this->app->runningUnitTests();
	}

	/**
	 * Determine whether a JSON response should be returned.
	 *
	 * @return bool
	 */
	protected function requestIsJson(Request $request = null)
	{
		if ($request === null) $request = $this->app['request'];

		return $request->wantsJson() || $request->isJson() || $request->ajax();
	}
}
