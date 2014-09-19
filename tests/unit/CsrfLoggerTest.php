<?php
namespace anlutro\L4SmartErrors\Tests;

use PHPUnit_Framework_TestCase;
use Mockery as m;

use anlutro\L4SmartErrors\Log\CsrfLogger;

class CsrfLoggerTest extends PHPUnit_Framework_TestCase
{
	public function tearDown()
	{
		m::close();
	}

	/** @test */
	public function logsWithAppInfoString()
	{
		$logger = new CsrfLogger(
			$log = m::mock('Psr\Log\LoggerInterface'),
			$appInfo = m::mock('anlutro\L4SmartErrors\AppInfoGenerator')
		);

		$appInfo->shouldReceive('renderCompact')->once()
			->andReturn('AppInfoGenerator string');
		$log->shouldReceive('warning')->once()->andReturnUsing(function($str) {
			$this->assertContains('CSRF token mismatch', $str);
			$this->assertContains('handled by L4SmartErrors', $str);
			$this->assertContains('AppInfoGenerator string', $str);
		});

		$logger->log();
	}
}
