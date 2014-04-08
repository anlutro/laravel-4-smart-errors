<?php
namespace anlutro\L4SmartErrors\Tests;

use anlutro\LaravelTesting\PkgAppTestCase;
use Mockery as m;

class ErrorHandlingTest extends PkgAppTestCase
{
	public function getVendorPath()
	{
		return __DIR__.'/../../vendor';
	}

	public function getExtraProviders()
	{
		return ['anlutro\L4SmartErrors\L4SmartErrorsServiceProvider'];
	}

	public function setUp()
	{
		parent::setUp();
		$this->app['env'] = 'production';
		$this->app['config']->set('smarterror::dev-email', 'foo@bar.com');
		$this->app['config']->set('app.debug', false);
		$this->app['config']->set('mail.driver', 'sendmail');
		$this->app['config']->set('mail.pretend', false);
		$this->app['config']->set('mail.from', ['name' => 'FooBar', 'address' => 'foo@bar.com']);

		$this->app['router']->get('exception', function() {
			throw new \LogicException('L4SmartErrors test exception');
		});

		$this->app['router']->get('alert', function() {
			$this->app['log']->alert('L4SmartErrors test alert', ['foo' => 'bar']);
			return 'Logged!';
		});
	}

	public function tearDown()
	{
		m::close();
	}

	/**
	 * Mock a class on Laravel's IoC container.
	 *
	 * @param  string $key The IoC key - 'mail', 'log' etc
	 *
	 * @return \Mockery\MockInterface
	 */
	public function mock($key)
	{
		$mock = m::mock(get_class($this->app[$key]));
		$this->app->instance($key, $mock);
		return $mock;
	}

	/**
	 * Define a set of strings that are expected to be in both the plaintext
	 * and HTML emails being sent by the error handler.
	 *
	 * @param  array  $strings
	 *
	 * @return void
	 */
	public function expectMailBodiesContain(array $strings)
	{
		$this->mock('swift.mailer')->shouldReceive('send')->once()
			->andReturnUsing(function($msg) use($strings) {
				$html = $msg->getBody(); $children = $msg->getChildren();
				$plain = $children[0]->getBody();
				foreach ([$html, $plain] as $body) {
					foreach ($strings as $string) {
						$this->assertContains($string, $body);
					}
				}
			});
	}

	/**
	 * Get the response object.
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function getResponse()
	{
		$response = $this->client->getResponse();

		// If an exception happens inside the exception handler, including
		// exceptions on failed assertions by PHPUnit, the exception will not be
		// thrown but instead a 200 response with a plain string content will be
		// returned. Therefore we do a manual check and fail if this happens
		if (strpos($response->getContent(), 'Error in exception handler') !== false) {
			$this->fail($response->getContent());
		}

		return $response;
	}

	/**
	 * Call an URL.
	 *
	 * @param  string $method HTTP method
	 * @param  string $path   Relative URL
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function call($method, $path)
	{
		// buffer the output because the exception handler is retarded
		// https://github.com/laravel/framework/pull/4073
		ob_start();
		$result = parent::call($method, $path);
		ob_end_clean();
		return $result;
	}

	/** @test */
	public function basicErrorHandling()
	{
		$this->expectMailBodiesContain([
			'LogicException', 'L4SmartErrors test exception', __FILE__,
		]);
		$this->call('get', '/exception');
		$response = $this->getResponse();
		$this->assertEquals(500, $response->getStatusCode());
		$this->assertContains(
			$this->app['translator']->get('smarterror::error.genericErrorTitle'),
			$response->getContent()
		);
	}

	/** @test */
	public function expandedExceptionTrace()
	{
		$this->app['config']->set('smarterror::expand-stack-trace', true);
		$this->expectMailBodiesContain([
			'LogicException', 'L4SmartErrors test exception', __FILE__,
		]);
		$this->call('get', '/exception');
		$response = $this->getResponse();
		$this->assertEquals(500, $response->getStatusCode());
		$this->assertContains(
			$this->app['translator']->get('smarterror::error.genericErrorTitle'),
			$response->getContent()
		);
	}

	/** @test */
	public function alertLoggerHandling()
	{
		$this->expectMailBodiesContain([
			'An event with the highest possible logging level, ALERT, was registered',
			'L4SmartErrors test alert',
		]);
		
		$this->call('get', '/alert');
		$response = $this->getResponse();
		$this->assertEquals('Logged!', $response->getContent());
	}

	/** @test */
	public function missingHandling()
	{
		$this->mock('log')->shouldReceive('warning')->once()
			->with('404 for URL http://localhost/does/not/exist -- Referer: none');
		$this->call('get', '/does/not/exist');
		$response = $this->getResponse();
		$this->assertEquals(404, $response->getStatusCode());
		$this->assertContains(
			$this->app['translator']->get('smarterror::error.missingTitle'),
			$response->getContent()
		);
	}
}
