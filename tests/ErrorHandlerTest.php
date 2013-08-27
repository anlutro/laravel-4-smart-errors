<?php
require_once __DIR__ . '/../vendor/autoload.php';

use anlutro\L4SmartErrors\ErrorHandler;
use anlutro\L4ConfigMock\ConfigMock;
use Mockery as m;

class ExceptionHandlingTest extends PHPUnit_Framework_TestCase
{
	public function setUp()
	{
		$pkg = 'anlutro/l4-smart-errors';

		$this->handler = new ErrorHandler($pkg);

		$this->config = new ConfigMock;
		$this->config->load(__DIR__ . '/../src/config/config.php', $pkg);
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

		// ugly php 5.3 hack
		$self = $this;

		$this->logger->shouldReceive('error')->once()
			->with(m::on(function($logged) use($self) {
				$self->assertContains('Route: action', $logged);
				$self->assertContains('URL: url', $logged);
				return true;
			}));

		$this->handler->handleException($exception);
	}

	public function testExceptionHandlerWithInput()
	{
		$exception = new Exception('test');
		$input = array('key' => 'val');
		$this->config->set('app.debug', true);

		$this->router->shouldReceive('currentRouteAction')
			->andReturn('action');

		$this->request->shouldReceive('fullUrl')
			->andReturn('url');
		$this->request->shouldReceive('all')
			->andReturn($input);

		// ugly php 5.3 hack
		$self = $this;

		$this->logger->shouldReceive('error')->once()
			->with(m::on(function($logged) use($self, $input) {
				$self->assertContains('Input: '.json_encode($input), $logged);
				return true;
			}));

		$this->handler->handleException($exception);
	}

	public function testRealExceptionHandler()
	{
		$exception = new Exception('test');
		$route = 'action';
		$url = 'url';
		$root = 'root';
		$input = array('test' => 'input');
		$this->config->set('app.debug', false);

		$this->router->shouldReceive('currentRouteAction')
			->andReturn($route);
		$this->request->shouldReceive('fullUrl')
			->andReturn($url);
		$this->request->shouldReceive('root')
			->andReturn($root);
		$this->request->shouldReceive('all')
			->andReturn($input);

		$this->logger->shouldReceive('error')->once();

		// php 5.3 hack
		$self = $this;

		$this->mailer->shouldReceive('send')->once()
			->with('anlutro/l4-smart-errors::email', m::on(function($mailData) use($self, $exception, $url, $route, $input) {
				$self->assertContains($exception, $mailData);
				$self->assertContains($url, $mailData);
				$self->assertContains($route, $mailData);
				$self->assertContains($input, $mailData);
				return true;
			}), m::on(function($closure) use ($root) {
				$message = m::mock('message');
				$message->shouldReceive('to')
					->with('test@example.com')
					->andReturn(m::self());
				$message->shouldReceive('subject')
					->with('Error report - uncaught exception - '.$root)
					->andReturn(m::self());
				$closure($message);
				return true;
			}));

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

	public function testMissingHandlerWhenDebugTrue()
	{
		$exception = new Exception('test');
		$this->config->set('app.debug', true);

		$this->request->shouldReceive('fullUrl')
			->andReturn('url');
		$this->request->shouldReceive('header')
			->with('referer')
			->andReturn('referer');

		$this->logger->shouldReceive('warning')->once();

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
