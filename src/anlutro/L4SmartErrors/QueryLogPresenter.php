<?php
/**
 * Laravel 4 Smart Errors
 *
 * @author    Andreas Lutro <anlutro@gmail.com>
 * @license   http://opensource.org/licenses/MIT
 * @package   l4-smart-errors
 */

namespace anlutro\L4SmartErrors;

class QueryLogPresenter extends AbstractPresenter
{
	protected $queryLog;

	public function __construct(array $queryLog)
	{
		$this->queryLog = $queryLog;
	}

	public function render()
	{
		return $this->renderVarDump($this->queryLog);
	}

	public function __toString()
	{
		return $this->render();
	}
}
