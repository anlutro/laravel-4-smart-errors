<?php

class ExceptionPresenterTest extends PHPUnit_Framework_TestCase
{
	public function testExceptionInfoString()
	{
		$exception = new StubException('Test exception', 100);
		$presenter = $this->makePresenter($exception);
		$str = $presenter->renderInfoPlain();
		$this->assertContains('StubException', $str);
		$this->assertContains('Test exception', $str);
		$this->assertContains('100', $str);
		$this->assertContains(__FILE__, $str);
	}

	public function testExceptionStrackTraceString()
	{
		$exception = new Exception;
		$presenter = $this->makePresenter($exception);
		$trace = $presenter->renderTracePlain();

		$this->assertContains(__FUNCTION__, $trace);
		$this->assertContains(__CLASS__, $trace);
	}

	public function testExceptionDetailedStrackTraceString()
	{
		$exception = new Exception;
		$presenter = $this->makePresenter($exception);
		$presenter->setDescriptive(true);
		$trace = $presenter->renderTracePlain();

		$this->assertContains(__FUNCTION__, $trace);
		$this->assertContains(__CLASS__, $trace);
	}

	public function makePresenter(Exception $exception)
	{
		return new anlutro\L4SmartErrors\ExceptionPresenter($exception);
	}
}

class StubException extends Exception {}
