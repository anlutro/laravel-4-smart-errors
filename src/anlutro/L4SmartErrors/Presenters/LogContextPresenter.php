<?php
/**
 * Laravel 4 Smart Errors
 *
 * @author    Andreas Lutro <anlutro@gmail.com>
 * @license   http://opensource.org/licenses/MIT
 * @package   l4-smart-errors
 */

namespace anlutro\L4SmartErrors\Presenters;

class LogContextPresenter extends AbstractPresenter
{
	protected $context;

	public function __construct(array $context)
	{
		$this->context = $context;
	}

	public function renderHtml()
	{
		return $this->renderVarDump($this->context, true);
	}

	public function renderPlain()
	{
		return $this->renderVarDump($this->context, false);
	}

	public function renderCompact()
	{
		return json_encode($this->context);
	}
}
