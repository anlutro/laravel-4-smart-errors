<?php

use Mockery as m;

class ContextCollectorTest extends PHPUnit_Framework_TestCase
{
	/** @test */
	public function input_is_sanitized()
	{
		$input = ['foo' => 'bar', 'password' => 'baz'];
		$context = $this->getContext(['input' => $input]);
		$this->assertEquals('HIDDEN', $context['input']['password']);
	}

	public function getContext(array $data)
	{
		$app = new \Illuminate\Foundation\Application();
		$app['env'] = 'testing';
		$app['session'] = m::mock('Illuminate\Session\Store');
		$app['session']->shouldReceive('getId')->andReturn('session_id');
		$app['request'] = new \Illuminate\Http\Request([], $data['input']);
		$app['request']->setMethod('POST');
		return (new anlutro\L4SmartErrors\Log\ContextCollector($app, false))
			->getContext();
	}
}
