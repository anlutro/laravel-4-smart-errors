<?php
namespace anlutro\L4SmartErrors\Tests;

use PHPUnit_Framework_TestCase;
use Mockery as m;

use anlutro\L4SmartErrors\Log\ExceptionLogger;

class ExceptionLoggerTest extends PHPUnit_Framework_TestCase
{
	public function tearDown()
	{
		m::close();
	}

	/** @test */
	public function logsWithExceptionAppInfoAndInput()
	{
		$logger = new ExceptionLogger(
			$log = m::mock('Illuminate\Log\Writer'),
			$appInfo = m::mock('anlutro\L4SmartErrors\AppInfoGenerator'),
			$input = m::mock('anlutro\L4SmartErrors\Presenters\InputPresenter')
		);

		$appInfo->shouldReceive('renderCompact')->once()
			->andReturn('AppInfoGenerator string');
		$input->shouldReceive('renderCompact')->once()
			->andReturn('InputPresenter string');
		$log->shouldReceive('error')->once()->andReturnUsing(function($str) {
			$this->assertContains('Uncaught Exception', $str);
			$this->assertContains('handled by L4SmartErrors', $str);
			$this->assertContains('AppInfoGenerator string', $str);
			$this->assertContains('InputPresenter string', $str);
			$this->assertContains(__FILE__, $str);
			$this->assertContains(__CLASS__, $str);
		});

		$logger->log(new \Exception);
	}
}
