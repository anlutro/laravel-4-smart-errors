<?php
/**
 * Laravel 4 Smart Errors
 *
 * @author    Andreas Lutro <anlutro@gmail.com>
 * @license   http://opensource.org/licenses/MIT
 * @package   l4-smart-errors
 */

namespace anlutro\L4SmartErrors\Presenters;
use anlutro\L4SmartErrors\Traits\SanitizeTrait;

class InputPresenter extends AbstractPresenter
{
	use SanitizeTrait;

	protected $input;

	public function __construct(array $input, array $fields)
	{
		$this->input = $this->sanitize($input, $fields);
	}

	public function renderHtml()
	{
		return $this->renderVarDump($this->input, true);
	}

	public function renderPlain()
	{
		return $this->renderVarDump($this->input, false);
	}

	public function renderCompact()
	{
		return json_encode($this->input);
	}
}
