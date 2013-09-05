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
		$this->request->shouldReceive('getClientIp')
			->andReturn('client');

		// ugly php 5.3 hack
		$self = $this;

		$this->logger->shouldReceive('error')->once()
			->with(m::on(function($logged) use($self) {
				$self->assertContains('Route: action', $logged);
				$self->assertContains('URL: url', $logged);
				$self->assertContains('Client: client', $logged);
				return true;
			}));

		$this->handler->handleException($exception);
	}

	public function testExceptionHandlerWithInput()
	{
		$exception = new Exception('test');
		$input = array('key' => 'val');
		$client = '1.2.3.4';
		$this->config->set('app.debug', true);

		$this->router->shouldReceive('currentRouteAction')
			->andReturn('action');

		$this->request->shouldReceive('fullUrl')
			->andReturn('url');
		$this->request->shouldReceive('all')
			->andReturn($input);
		$this->request->shouldReceive('getClientIp')
			->andReturn($client);

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
		$this->config->set('app.debug', false);
		$exception = new Exception('test');
		$route = 'action';
		$url = 'url';
		$root = 'root';
		$input = array('test' => 'input');
		$client = '1.2.3.4';

		$this->router->shouldReceive('currentRouteAction')
			->andReturn($route);
		$this->request->shouldReceive('fullUrl')
			->andReturn($url);
		$this->request->shouldReceive('root')
			->andReturn($root);
		$this->request->shouldReceive('all')
			->andReturn($input);
		$this->request->shouldReceive('getClientIp')
			->andReturn($client);

		$this->logger->shouldReceive('error')->once();

		// php 5.3 hack
		$self = $this;

		$this->mailer->shouldReceive('send')->once()
			->with(
				array('anlutro/l4-smart-errors::email', 'anlutro/l4-smart-errors::error_email_plain'),
				m::on(function($mailData) use($self, $exception, $url, $route, $input, $client) {
					$self->assertContains($exception, $mailData);
					$self->assertContains($url, $mailData);
					$self->assertContains($route, $mailData);
					$self->assertContains($input, $mailData);
					$self->assertContains($client, $mailData);
					return true;
				}
			), m::on(function($closure) use ($root) {
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

	public function testForceMail()
	{
		$this->config->set('app.debug', false);
		$this->config->set('mail.pretend', true);
		$this->config->set('anlutro/l4-smart-errors::force_email', true);
		$this->handler->setConfig($this->config);

		$exception = new Exception('test');
		$route = 'action';
		$url = 'url';
		$root = 'root';
		$input = array('test' => 'input');
		$client = '1.2.3.4';

		$this->router->shouldReceive('currentRouteAction')
			->andReturn($route);
		$this->request->shouldReceive('fullUrl')
			->andReturn($url);
		$this->request->shouldReceive('root')
			->andReturn($root);
		$this->request->shouldReceive('all')
			->andReturn($input);
		$this->request->shouldReceive('getClientIp')
			->andReturn($client);

		$this->logger->shouldReceive('error')->once();

		$this->mailer->shouldReceive('send')->once();

		$this->view->shouldReceive('make')->once();

		$this->assertTrue($this->config->get('mail.pretend'));

		$this->handler->handleException($exception);

		$this->assertFalse($this->config->get('mail.pretend'));
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

		// php 5.3 hack
		$self = $this;

		$this->mailer->shouldReceive('send')->once()
			->with(
				array('anlutro/l4-smart-errors::alert_email', 'anlutro/l4-smart-errors::alert_email_plain'),
				m::on(function($data) use($self) {
					$self->assertContains('action', $data);
					$self->assertContains('url', $data);
					$self->assertContains('test', $data);
					$self->assertContains(array('key' => 'val'), $data);
					return true;
				}),
				m::on(function($closure) {
					$message = m::mock();
					$message->shouldReceive('to')
						->with('test@example.com')
						->andReturn(m::self());
					$message->shouldReceive('subject')
						->with('Alert logged - root');
					$closure($message);
					return true;
				})
			);

		$this->handler->handleAlert('test', array('key' => 'val'));
	}
}
