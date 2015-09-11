<?php
/**
 * Laravel 4 Smart Errors
 *
 * @author    Andreas Lutro <anlutro@gmail.com>
 * @license   http://opensource.org/licenses/MIT
 * @package   l4-smart-errors
 */

namespace anlutro\L4SmartErrors\Traits;

trait SanitizeTrait
{
	/**
	 * Sanitize the $data array by overwriting any keys found in $sanitize
	 * with "HIDDEN".
	 *
	 * @return array
	 */
	protected function sanitize($data, $sanitize)
	{
		foreach ($data as $key => &$value) {
			if (is_array($value)) {
				$value = $this->sanitize($value, $sanitize);
			}
			foreach ($sanitize as $field) {
				if (strpos(strtolower($key), $field) !== false) {
					$value = 'HIDDEN';
				}
			}
		}

		return $data;
	}
}
