<?php
namespace anlutro\L4SmartErrors\Traits;

use Illuminate\Foundation\Application;

trait ConfigCompatibilityTrait
{
	protected static $_is_l5;

	protected function getConfig($key, $default = null)
	{
		return $this->_getConfig()
			->get($this->_getConfigKey($key), $default);
	}

	protected function setConfig($key, $value)
	{
		return $this->_getConfig()
			->set($this->_getConfigKey($key), $value);
	}

	private function _getConfigKey($key)
	{
		if (static::$_is_l5 === null) {
			static::$_is_l5 = version_compare(Application::VERSION, '5.0', '>=');
		}
		if (static::$_is_l5) {
			$key = str_replace('::', '.', $key);
		}

		return $key;
	}

	private function _getConfig()
	{
		if (isset($this->config)) {
			return $this->config;
		} elseif (isset($this->app)) {
			return $this->app['config'];
		}
	}
}