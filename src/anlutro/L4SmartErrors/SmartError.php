<?php
/**
 * Laravel 4 Smart Errors
 *
 * @author    Andreas Lutro <anlutro@gmail.com>
 * @license   http://opensource.org/licenses/MIT
 * @package   l4-smart-errors
 */

namespace anlutro\L4SmartErrors;

use Illuminate\Support\Facades\Facade;

class SmartError extends Facade
{
	static protected function getFacadeAccessor()
	{
		return 'smarterror';
	}
}
