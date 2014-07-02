<?php
namespace anlutro\L4SmartErrors\Tests;

use PHPUnit_Framework_TestCase;
use Mockery as m;

class ExceptionMailerTest extends PHPUnit_Framework_TestCase
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

	protected function mockExceptionPresenter($exception)
	{
		$mock = m::mock('anlutro\L4SmartErrors\Presenters\ExceptionPresenter');
		if ($exception) {
			$mock->shouldReceive('getException')->andReturn($exception);
		}
		return $mock;
	}

	protected function mockAppInfoGenerator()
	{
		return m::mock('anlutro\L4SmartErrors\AppInfoGenerator');
	}

	protected function makeInputPresenter($input = array())
	{
		return new \anlutro\L4SmartErrors\Presenters\InputPresenter($input);
	}

	protected function makeQueryLogPresenter($queryLog = array())
	{
		return new \anlutro\L4SmartErrors\Presenters\QueryLogPresenter($queryLog);
	}

	protected function makeMailer($app, $exception, $appInfo, $input = null, $queryLog = null)
	{
		return new \anlutro\L4SmartErrors\Mail\ExceptionMailer($app, $exception, $appInfo, $input, $queryLog);
	}

	protected function makeEverything()
	{
		$app = $this->makeApp();
		$exception = $this->mockExceptionPresenter(new TestException);
		$appInfo = $this->mockAppInfoGenerator();
		$input = $this->makeInputPresenter();
		$queryLog = $this->makeQueryLogPresenter();
		$mailer = $this->makeMailer($app, $exception, $appInfo, $input, $queryLog);
		return [$mailer, $app, $exception, $appInfo, $input, $queryLog];
	}

	/** @test */
	public function everything()
	{
		list($mailer, $app, $exception, $appInfo, $input, $queryLog) = $this->makeEverything();

		$app['request']->shouldReceive('root')->once()->andReturn('http://localhost');
		$app['mailer']->shouldReceive('send')->once()
			->with(m::type('array'), m::type('array'), m::type('Closure'))
			->andReturnUsing(function($v, $d, \Closure $callback) {
				$msg = m::mock('Illuminate\Mail\Message');
				$msg->shouldReceive('to')->once()->with('foo@bar.com')->andReturn(m::self())->getMock()->shouldReceive('subject')->once()->with('[production] TestException - http://localhost');
				$callback($msg);
			});
		
		$mailer->send('foo@bar.com');
	}
}

class TestException extends \Exception {}
