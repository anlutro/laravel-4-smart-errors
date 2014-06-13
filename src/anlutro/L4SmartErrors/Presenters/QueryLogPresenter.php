<?php
/**
 * Laravel 4 Smart Errors
 *
 * @author    Andreas Lutro <anlutro@gmail.com>
 * @license   http://opensource.org/licenses/MIT
 * @package   l4-smart-errors
 */

namespace anlutro\L4SmartErrors\Presenters;

class QueryLogPresenter extends AbstractPresenter
{
	protected $queryLog;

	public function __construct(array $queryLog)
	{
		$this->queryLog = $queryLog;
	}

	public function renderHtml()
	{
		return $this->renderVarDump($this->queryLog, true);
	}

	public function renderPlain()
	{
		return $this->renderVarDump($this->queryLog, false);
	}

	public function renderCompact()
	{
		// @todo Improve this
		return json_encode($this->queryLog);
	}
}
