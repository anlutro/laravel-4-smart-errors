<?php
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
