<?php

use Mockery as m;

class ErrorHandlerTest extends PHPUnit_Framework_TestCase
{
	public function tearDown()
	{
		m::close();
	}

	protected function makeHandler($app)
	{
		return new anlutro\L4SmartErrors\ErrorHandler($app);
	}

	protected function makeApplication(array $binding = array())
	{
		$app = new Illuminate\Foundation\Application;
		$app['env'] = 'testing';
		$this->setConfig($app, $this->getDefaultConfig());
		$this->setLogger($app);
		$this->setMailer($app);
		$this->setView($app);
		$this->setRequest($app);
		foreach ($binding as $key => $value) {
			$app->instance($key, $value);
		}
		Illuminate\Support\Facades\Facade::setFacadeApplication($app);
		return $app;
	}

	protected function getDefaultConfig()
	{
		return array(
			'app.debug' => false,
			'smarterror::dev-email' => 'test@test.com',
			'smarterror::force-email' => false,
			'smarterror::error-email-view' => null,
			'smarterror::error-email-view-plain' => null,
			'smarterror::alert-email-view' => null,
			'smarterror::alert-email-view-plain' => null,
			'smarterror::error-view' => null,
			'smarterror::missing-view' => null,
			'smarterror::date-format' => null,
			'smarterror::expand-stack-trace' => false,
			'smarterror::include-query-log' => false,
		);
	}

	protected function setConfig($app, array $config)
	{
		$mockConfig = m::mock('Illuminate\Config\Repository');
		$mockConfig->shouldReceive('get')->andReturnUsing(function($key) use(&$config) {
			return isset($config[$key]) ? $config[$key] : null;
		});
		$mockConfig->shouldReceive('set')->andReturnUsing(function($key, $value) use(&$config) {
			$config[$key] = $value;
		});
		$app['config'] = $mockConfig;
		return $mockConfig;
	}

	protected function setLogger($app)
	{
		$app['log'] = m::mock('Illuminate\Log\Writer');
	}

	protected function setMailer($app)
	{
		$app['mailer'] = m::mock('Illuminate\Mail\Mailer');
	}

	protected function setView($app)
	{
		$app['view'] = m::mock('Illuminate\View\Environment');
	}

	public function setRequest($app)
	{
		$app['request'] = m::mock('Illuminate\Http\Request');
		$app['request']->shouldReceive('root')->andReturn('http://foo.com');
		$app['request']->shouldReceive('fullUrl')->andReturn('http://foo.com/bar');
		$this->setInput($app, array());
		$this->setJsonRequest($app, false);
	}

	public function setInput($app, array $data)
	{
		$app['request']->shouldReceive('all')->andReturn($data);
	}

	public function setJsonRequest($app, $toggle)
	{
		$toggle = (bool) $toggle;
		foreach (array('isJson', 'wantsJson', 'ajax') as $method) {
			$app['request']->shouldReceive($method)->andReturn($toggle);
		}
	}

	public function testMakeHandler()
	{
		$app = $this->makeApplication();
		$handler = $this->makeHandler($app);
		$this->assertInstanceOf('anlutro\L4SmartErrors\ErrorHandler', $handler);
	}

	public function testHandleException()
	{
		$handler = $this->makeHandler($app = $this->makeApplication());

		$app['log']->shouldReceive('error')->with(m::on(function($str) {
			$this->assertContains('Uncaught Exception (handled by L4SmartErrors)', $str);
			return true;
		}));

		$app['mailer']->shouldReceive('send')->with(
			array('smarterror::error-email', 'smarterror::error-email-plain'),
			m::on(function($data) {
				if (!is_array($data)) return false;
				$this->assertArrayHasKey('info', $data);
				$this->assertInstanceOf('anlutro\L4SmartErrors\AppInfoPresenter', $data['info']);
				$this->assertArrayHasKey('exception', $data);
				$this->assertInstanceOf('anlutro\L4SmartErrors\ExceptionPresenter', $data['exception']);
				$this->assertArrayHasKey('input', $data);
				$this->assertInstanceOf('anlutro\L4SmartErrors\InputPresenter', $data['input']);
				$this->assertArrayHasKey('queryLog', $data);
				$this->assertFalse($data['queryLog']);
				return true;
			}),
			m::on(function($closure) {
				if (!$closure instanceof Closure) return false;
				$mail = m::mock('Illuminate\Mail\Message');
				$mail->shouldReceive('to')->once()->with('test@test.com')->andReturn(m::self())
					->getMock()->shouldReceive('subject')->once()->with('Error report - uncaught exception - http://foo.com');
				$closure($mail);
				return true;
			})
		);

		$app['view']->shouldReceive('make')->with('smarterror::generic', m::type('array'));

		$handler->handleException(new Exception);
	}

	public function testHandleExceptionWhenDebugIsTrue()
	{
		$handler = $this->makeHandler($app = $this->makeApplication());

		$app['config']->set('app.debug', true);

		$app['log']->shouldReceive('error')->with(m::on(function($str) {
			$this->assertContains('Uncaught Exception (handled by L4SmartErrors)', $str);
			return true;
		}));

		$handler->handleException(new Exception);
	}

	public function testHandleExceptionWhenEmailIsNull()
	{
		$handler = $this->makeHandler($app = $this->makeApplication());

		$app['config']->set('smarterror::dev-email', null);

		$app['log']->shouldReceive('error')->with(m::on(function($str) {
			$this->assertContains('Uncaught Exception (handled by L4SmartErrors)', $str);
			return true;
		}));

		$app['view']->shouldReceive('make')->with('smarterror::generic', m::type('array'));

		$handler->handleException(new Exception);
	}
}
