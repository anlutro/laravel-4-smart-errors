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
	public function log()
	{
		$logger = new CsrfLogger(
			$log = m::mock('Psr\Log\LoggerInterface'),
			$context = m::mock('anlutro\L4SmartErrors\Log\ContextCollector'),
			'session_token', 'input_token'
		);

		$context->shouldReceive('getContext')->once()
			->andReturn(['foo' => 'bar']);
		$log->shouldReceive('warning')->once()->andReturnUsing(function($str, $context) {
			$this->assertContains('CSRF token mismatch', $str);
			$this->assertContains('session value: session_token', $str);
			$this->assertContains('input value: input_token', $str);
			$this->assertArrayHasKey('foo', $context);
			$this->assertEquals('bar', $context['foo']);
		});

		$logger->log();
	}
}
