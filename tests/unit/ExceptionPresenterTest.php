<?php

class ExceptionPresenterTest extends PHPUnit_Framework_TestCase
{
	public function testExceptionInfoString()
	{
		$exception = new StubException('Test exception', 100);
		$presenter = $this->makePresenter($exception);
		$this->assertContains('StubException', $presenter->info);
		$this->assertContains('Test exception', $presenter->info);
		$this->assertContains('100', $presenter->info);
		$this->assertContains(__FILE__, $presenter->info);
	}

	public function testExceptionStrackTraceString()
	{
		$exception = new Exception;
		$presenter = $this->makePresenter($exception);
		$trace = $presenter->trace;

		$this->assertContains(__FUNCTION__, $trace);
		$this->assertContains(__CLASS__, $trace);
	}

	public function testExceptionDetailedStrackTraceString()
	{
		$exception = new Exception;
		$presenter = $this->makePresenter($exception);
		$presenter->setDescriptive(true);
		$trace = $presenter->trace;

		$this->assertContains(__FUNCTION__, $trace);
		$this->assertContains(__CLASS__, $trace);
	}

	public function makePresenter(Exception $exception)
	{
		return new anlutro\L4SmartErrors\ExceptionPresenter($exception);
	}
}

class StubException extends Exception {}
