<?php
class Config
{
	protected $items = array();

	public function __construct($items)
	{
		foreach ($items as $key => $val) {
			$this->items[$key] = 'anlutro/l4-smart-errors::' . $val;
		}
	}

	public function get($key)
	{
		if (isset($this->items[$key]))
			return $this->items[$key];
		else
			return null;
	}

	public function set($key, $value)
	{
		$this->items[$key] = $value;
	}
}
