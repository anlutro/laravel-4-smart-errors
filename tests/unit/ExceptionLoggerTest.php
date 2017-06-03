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
	public function log()
	{
		$logger = new ExceptionLogger(
			$log = m::mock('Psr\Log\LoggerInterface'),
			$context = m::mock('anlutro\L4SmartErrors\Log\ContextCollector')
		);

		$context->shouldReceive('getContext')->once()
			->andReturn(['foo' => 'bar']);
		$log->shouldReceive('error')->once()->andReturnUsing(function($str, $context) {
			$this->assertRegexp("/Uncaught (exception 'Exception'|Exception)/", $str);
			$this->assertContains(__FILE__, $str);
			$this->assertContains(__CLASS__, $str);
			$this->assertEquals('bar', $context['foo']);
		});

		$logger->log(new \Exception);
	}
}
