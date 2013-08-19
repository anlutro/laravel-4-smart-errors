<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/mocks/Config.php';

use anlutro\L4SmartErrors\ErrorHandler;
use Mockery as m;

class ExceptionHandlingTest extends PHPUnit_Framework_TestCase
{
	public function setUp()
	{
		$pkg = 'anlutro/l4-smart-errors';

		$this->handler = new ErrorHandler($pkg);

		$config = include __DIR__ . '/../src/config/config.php';
		$this->config = new Config($config);
		$this->config->set($pkg.'::dev_email', 'test@example.com');
		$this->config->set($pkg.'::force_email', false);
		$this->handler->setConfig($this->config);

		$this->mailer = m::mock('Mailer');
		$this->handler->setMailer($this->mailer);

		$this->logger = m::mock('Logger');
		$this->handler->setLogger($this->logger);

		$this->request = m::mock('Request');
		$this->handler->setRequest($this->request);

		$this->router = m::mock('Router');
		$this->handler->setRouter($this->router);

		$this->view = m::mock('View');
		$this->handler->setView($this->view);
	}

	public function tearDown()
	{
		m::close();
	}

	public function testExceptionHandler()
	{
		$exception = new Exception('test');
		$this->config->set('app.debug', true);

		$this->router->shouldReceive('currentRouteAction')
			->andReturn('action');
		$this->request->shouldReceive('fullUrl')
			->andReturn('url');
		$this->request->shouldReceive('all')
			->andReturn(array());

		$this->logger->shouldReceive('error')->once()
			->andReturnUsing(function($logstr) {
				$this->assertContains('Route: action', $logstr);
				$this->assertContains('URL: url', $logstr);
			});

		$this->handler->handleException($exception);
	}

	public function testExceptionHandlerWithInput()
	{
		$exception = new Exception('test');
		$this->config->set('app.debug', true);

		$this->router->shouldReceive('currentRouteAction')
			->andReturn('action');
		$this->request->shouldReceive('fullUrl')
			->andReturn('url');
		$this->request->shouldReceive('all')
			->andReturn(array('key' => 'val'));

		$this->logger->shouldReceive('error')->once()
			->andReturnUsing(function($logstr) {
				$this->assertContains('Input: '.json_encode(array('key' => 'val')), $logstr);
			});

		$this->handler->handleException($exception);
	}

	public function testRealExceptionHandler()
	{
		$exception = new Exception('test');
		$this->config->set('app.debug', false);

		$this->router->shouldReceive('currentRouteAction')
			->andReturn('action');
		$this->request->shouldReceive('fullUrl')
			->andReturn('url');
		$this->request->shouldReceive('root')
			->andReturn('root');
		$this->request->shouldReceive('all')
			->andReturn(array());

		$this->logger->shouldReceive('error')->once();

		$this->mailer->shouldReceive('send')->once();

		$this->view->shouldReceive('make')->once();

		$this->handler->handleException($exception);
	}

	public function testMissingHandler()
	{
		$exception = new Exception('test');
		$this->config->set('app.debug', false);

		$this->request->shouldReceive('fullUrl')
			->andReturn('url');
		$this->request->shouldReceive('header')
			->with('referer')
			->andReturn('referer');

		$this->logger->shouldReceive('warning')->once();

		$this->view->shouldReceive('make')->once();

		$this->handler->handleMissing($exception);
	}

	public function testAlertHandlerDoesNothingOnDebugTrue()
	{
		$this->config->set('app.debug', true);
		
		$this->handler->handleAlert('test', array());
	}

	public function testAlertHandler()
	{
		$this->config->set('app.debug', false);

		$this->router->shouldReceive('currentRouteAction')
			->andReturn('action');
		$this->request->shouldReceive('fullUrl')
			->andReturn('url');
		$this->request->shouldReceive('root')
			->andReturn('root');

		$this->mailer->shouldReceive('send')->once();

		$this->handler->handleAlert('test', array());
	}
}
