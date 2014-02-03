<?php
namespace anlutro\L4SmartErrors;

class InputPresenter extends AbstractPresenter
{
	protected $input;

	public function __construct(array $input)
	{
		$this->input = $input;
	}

	public function render()
	{
		return $this->renderVarDump($this->input);
	}

	public function renderHtml()
	{
		return $this->renderVarDump($this->input, true);
	}

	public function renderCompact()
	{
		return json_encode($this->input);
	}

	public function __toString()
	{
		return $this->render();
	}
}
