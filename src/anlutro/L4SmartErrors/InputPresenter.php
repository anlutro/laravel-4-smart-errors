<?php
/**
 * Laravel 4 Smart Errors
 *
 * @author    Andreas Lutro <anlutro@gmail.com>
 * @license   http://opensource.org/licenses/MIT
 * @package   l4-smart-errors
 */

namespace anlutro\L4SmartErrors;

class InputPresenter extends AbstractPresenter
{
	protected $input;

	public function __construct(array $input)
	{
		$this->input = $input;
	}

	public function renderPlain()
	{
		return $this->renderVarDump($this->input, false);
	}

	public function renderHtml()
	{
		return $this->renderVarDump($this->input, true);
	}

	public function renderCompact()
	{
		return json_encode($this->input);
	}

	public function render()
	{
		return $this->html ? $this->renderHtml() : $this->renderPlain();
	}

	public function __toString()
	{
		return $this->render();
	}
}
