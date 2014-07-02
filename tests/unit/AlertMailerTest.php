<?php
namespace anlutro\L4SmartErrors\Tests;

use PHPUnit_Framework_TestCase;
use Mockery as m;

class Test extends PHPUnit_Framework_TestCase
{
	public function tearDown()
	{
		m::close();
	}

	protected function mockConfig(array $data)
	{
		return m::mock('Illuminate\Config\Repository')->shouldReceive('get')->andReturnUsing(function($key, $default = null) use($data) {
			return array_get($data, $key, $default);
		})->getMock();
	}

	protected function makeApp(array $config = array())
	{
		$app = new \Illuminate\Foundation\Application;
		$app->detectEnvironment(function() { return 'production'; });
		$app['config'] = $this->mockConfig($config);
		$app['request'] = m::mock('Illuminate\Http\Request');
		$app['mailer'] = m::mock('Illuminate\Mail\Mailer');
		return $app;
	}

	protected function mockAppInfoGenerator()
	{
		return m::mock('anlutro\L4SmartErrors\AppInfoGenerator');
	}

	protected function makeLogContextPresenter($context = array())
	{
		return new \anlutro\L4SmartErrors\Presenters\LogContextPresenter($context);
	}

	protected function makeMailer($app, $message, $context, $appInfo)
	{
		return new \anlutro\L4SmartErrors\Mail\AlertLogMailer($app, $message, $context, $appInfo);
	}

	protected function makeEverything($message)
	{
		$app = $this->makeApp();
		$context = $this->makeLogContextPresenter();
		$appInfo = $this->mockAppInfoGenerator();
		$mailer = $this->makeMailer($app, $message, $context, $appInfo);
		return [$mailer, $app, $context, $appInfo];
	}

	/** @test */
	public function everything()
	{
		list($mailer, $app, $context, $appInfo) = $this->makeEverything('test log message');
		
		$app['request']->shouldReceive('root')->once()->andReturn('http://localhost');
		$app['mailer']->shouldReceive('send')->once()
			->with(m::type('array'), m::type('array'), m::type('Closure'))
			->andReturnUsing(function($v, $d, \Closure $callback) {
				$msg = m::mock('Illuminate\Mail\Message');
				$msg->shouldReceive('to')->once()->with('foo@bar.com')->andReturn(m::self())->getMock()->shouldReceive('subject')->once()->with('[production] Alert logged - http://localhost');
				$callback($msg);
			});
		
		$mailer->send('foo@bar.com');
	}
}
