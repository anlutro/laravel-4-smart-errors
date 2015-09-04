<?php
/**
 * Laravel 4 Smart Errors
 *
 * @author    Andreas Lutro <anlutro@gmail.com>
 * @license   http://opensource.org/licenses/MIT
 * @package   l4-smart-errors
 */

namespace anlutro\L4SmartErrors\Presenters;

class SessionPresenter extends AbstractPresenter
{
	protected $session;

	public function __construct(array $session)
	{
		$this->session = $session;
	}

	public function renderHtml()
	{
		return $this->renderVarDump($this->session, true);
	}

	public function renderPlain()
	{
		return $this->renderVarDump($this->session, false);
	}

	public function renderCompact()
	{
		return json_encode($this->session);
	}
}
