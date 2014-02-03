<?php
/**
 * Laravel 4 Smart Errors
 *
 * @author    Andreas Lutro <anlutro@gmail.com>
 * @license   http://opensource.org/licenses/MIT
 * @package   l4-smart-errors
 */

namespace anlutro\L4SmartErrors;

use Exception;
use Xethron\L4ToString;

class ExceptionPresenter
{
	protected $exception;
	protected $previous;
	protected $descriptive = false;

	public function __construct(Exception $exception)
	{
		$this->exception = $exception;

		if ($previous = $exception->getPrevious()) {
			$this->previous = new static($previous);
		}
	}

	public function setDescriptive($toggle)
	{
		$this->descriptive = (bool) $toggle;
		return $this;
	}

	public function getPrevious()
	{
		return $this->previous;
	}

	public function renderExceptionInfo()
	{
		$str = get_class($this->exception)."\n";

		if ($this->exception->getMessage()) {
			$str .= 'Exception message: '.$this->exception->getMessage()."\n";
		}

		if ($this->exception->getCode()) {
			$str .= 'Exception code: '.$this->exception->getCode()."\n";
		}
		
		$str .= 'In '.$this->exception->getFile().' on line '.$this->exception->getLine();

		return $str;
	}

	public function renderStackTrace()
	{
		$str = $this->descriptive ?
			$this->getDescriptiveStackTrace() :
			$this->exception->getTraceAsString();

		return $str;
	}

	public function getDescriptiveStackTrace()
	{
		return L4ToString::exception($this->exception);
	}

	public function __get($key)
	{
		switch ($key) {
			case 'info':
				return $this->renderExceptionInfo();
				break;

			case 'trace':
				return $this->renderStackTrace();
				break;

			case 'previous':
				return $this->getPrevious();
				break;
			
			default:
				return null;
				break;
		}
	}

	public function __toString()
	{
		return $this->renderExceptionInfo() . "\n\n" . $this->renderStackTrace();
	}
}
