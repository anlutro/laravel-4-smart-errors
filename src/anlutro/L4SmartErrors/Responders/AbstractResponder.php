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
use anlutro\L4SmartErrors\Traits\ConsoleCheckingTrait;

abstract class AbstractResponder
{
	use ConsoleCheckingTrait;

	protected $app;

	public function __construct(Application $app)
	{
		$this->app = $app;
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
