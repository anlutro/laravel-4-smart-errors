<?php
/**
 * Laravel 4 Smart Errors
 *
 * @author    Andreas Lutro <anlutro@gmail.com>
 * @license   http://opensource.org/licenses/MIT
 * @package   l4-smart-errors
 */

namespace anlutro\L4SmartErrors;

class LogContextPresenter extends AbstractPresenter
{
	public function __construct(array $context)
	{
		$this->context = $context;
	}

	public function render()
	{
		return $this->renderVarDump($this->context, false);
	}

	public function renderHtml()
	{
		return $this->renderVarDump($this->context, true);
	}

	public function __toString()
	{
		return $this->render();
	}
}
