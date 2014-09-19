<?php
namespace anlutro\L4SmartErrors\Tests;

use PHPUnit_Framework_TestCase;
use Mockery as m;

use anlutro\L4SmartErrors\Log\MissingLogger;

class MissingLoggerTest extends PHPUnit_Framework_TestCase
{
	public function tearDown()
	{
		m::close();
	}

	/** @test */
	public function logsWithUrlAndReferer()
	{
		$logger = new MissingLogger(
			$log = m::mock('Psr\Log\LoggerInterface'),
			$request = m::mock('Illuminate\Http\Request')
		);

		$request->shouldReceive('fullUrl')->once()->andReturn('http://fake-url');
		$request->shouldReceive('header')->once()->with('referer')->andReturn('http://fake-referer');
		$log->shouldReceive('warning')->once()->andReturnUsing(function($str) {
			$this->assertContains('404 for URL http://fake-url', $str);
			$this->assertContains('Referer: http://fake-referer', $str);
			$this->assertContains('handled by L4SmartErrors', $str);
		});

		$logger->log();
	}
}
