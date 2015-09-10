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

	protected function makeSessionPresenter($session)
	{
		return new \anlutro\L4SmartErrors\Presenters\SessionPresenter($session, []);
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

	protected function makeMailer($app, $exception, $session, $appInfo, $input = null, $queryLog = null)
	{
		return new \anlutro\L4SmartErrors\Mail\ExceptionMailer($app, $exception, $session, $appInfo, $input, $queryLog);
	}

	protected function makeEverything()
	{
		$app = $this->makeApp(['smarterror::email-from' => 'bar@baz.com', 'smarterror::cc-email' => 'cc-me@example.com']);
		$exception = $this->mockExceptionPresenter(new TestException);
		$session = $this->makeSessionPresenter(['foo'=>'bar']);
		$appInfo = $this->mockAppInfoGenerator();
		$input = $this->makeInputPresenter();
		$queryLog = $this->makeQueryLogPresenter();
		$mailer = $this->makeMailer($app, $exception, $session, $appInfo, $input, $queryLog);
		return [$mailer, $app, $exception, $session, $appInfo, $input, $queryLog];
	}

	/** @test */
	public function everything()
	{
		list($mailer, $app, $exception, $session, $appInfo, $input, $queryLog) = $this->makeEverything();

		$app['request']->shouldReceive('root')->once()->andReturn('http://localhost');
		$app['mailer']->shouldReceive('send')->once()
			->with(m::type('array'), m::type('array'), m::type('Closure'))
			->andReturnUsing(function($v, $d, \Closure $callback) {
				$msg = m::mock('Illuminate\Mail\Message');
				$msg->shouldReceive('to')->once()->with('foo@bar.com');
				$msg->shouldReceive('from')->once()->with('bar@baz.com');
				$msg->shouldReceive('cc')->once()->with('cc-me@example.com');
				$msg->shouldReceive('subject')->once()->with('[production] TestException - http://localhost');
				$callback($msg);
			});
		
		$mailer->send('foo@bar.com');
	}
}

class TestException extends \Exception {}
