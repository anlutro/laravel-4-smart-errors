<?php
/**
 * Laravel 4 Smart Errors
 *
 * @author    Andreas Lutro <anlutro@gmail.com>
 * @license   http://opensource.org/licenses/MIT
 * @package   l4-smart-errors
 */

namespace anlutro\L4SmartErrors\Presenters;

use Xethron\L4ToString;

class ExceptionPresenter
{
	public $previous;
	protected $exception;
	protected $descriptive = false;

	public function __construct($exception)
	{
		$this->exception = $exception;

		if ($previous = $exception->getPrevious()) {
			$this->previous = new static($previous);
		}
	}

	public function getException()
	{
		return $this->exception;
	}

	public function getPrevious()
	{
		return $this->previous;
	}

	public function setDescriptive($toggle)
	{
		$this->descriptive = (bool) $toggle;
		return $this;
	}

	public function renderInfoPlain()
	{
		return implode("\n", $this->getExceptionInfo());
	}

	public function renderInfoHtml()
	{
		return implode('<br>', $this->getExceptionInfo());
	}

	public function getExceptionInfo()
	{
		$strings = array();
		$strings[] = get_class($this->exception);

		if ($this->exception->getMessage()) {
			$strings[] = 'Exception message: '.$this->exception->getMessage();
		}

		if ($this->exception->getCode()) {
			$strings[] = 'Exception code: '.$this->exception->getCode();
		}
		
		$strings[] = 'In '.$this->exception->getFile().' on line '.$this->exception->getLine();

		return $strings;
	}

	public function renderTracePlain()
	{
		return $this->descriptive ?
			$this->getDescriptiveStackTrace() :
			$this->exception->getTraceAsString();
	}

	public function renderTraceHtml()
	{
		return nl2br($this->renderTracePlain());
	}

	protected function getDescriptiveStackTrace()
	{
		return L4ToString::exception($this->exception);
	}

	public function __toString()
	{
		return $this->getExceptionInfo() . "\n\n" . $this->renderTracePlain();
	}
}
