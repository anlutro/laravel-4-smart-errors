<?php
namespace anlutro\L4SmartErrors;

abstract class AbstractPresenter
{
	protected $html = false;

	public function setHtml($toggle)
	{
		$this->html = (bool) $toggle;
		return $this;
	}

	public function renderVarDump($data)
	{
		ob_start();

		$xdebugHtml = extension_loaded('xdebug') && php_sapi_name() != 'cli';

		if ($this->html === false || $xdebugHtml === true) {
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
