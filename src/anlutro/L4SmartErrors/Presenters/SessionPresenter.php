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

class SessionPresenter extends AbstractPresenter
{
	use SanitizeTrait;

	protected $session;

	public function __construct(array $session, array $sanitizeFields)
	{
		$this->session = $this->sanitize($session, $sanitizeFields);
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
