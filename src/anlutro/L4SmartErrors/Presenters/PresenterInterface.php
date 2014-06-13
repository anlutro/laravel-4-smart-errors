<?php
namespace anlutro\L4SmartErrors\Presenters;

interface PresenterInterface
{
	public function renderHtml();
	public function renderPlain();
	public function renderCompact();
}
