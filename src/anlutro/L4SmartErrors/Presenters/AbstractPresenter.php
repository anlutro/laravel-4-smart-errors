<?php
/**
 * Laravel 4 Smart Errors
 *
 * @author    Andreas Lutro <anlutro@gmail.com>
 * @license   http://opensource.org/licenses/MIT
 * @package   l4-smart-errors
 */

namespace anlutro\L4SmartErrors\Presenters;

abstract class AbstractPresenter
{
	public function renderVarDump($data, $html)
	{
		$html = (bool) $html;
		$xdebugHtml = extension_loaded('xdebug') && php_sapi_name() != 'cli';

		ob_start();

		if ($html === false || $xdebugHtml === true) {
			var_dump($data);
		} else {
			echo '<pre style="white-space:pre-wrap;">';
			var_dump($data);
			echo '</pre>';
		}

		$str = ob_get_contents();
		ob_end_clean();

		return $str;
	}
}
