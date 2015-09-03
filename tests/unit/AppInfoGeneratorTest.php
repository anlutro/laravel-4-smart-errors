<?php
namespace anlutro\L4SmartErrors\Tests;

use PHPUnit_Framework_TestCase;
use Mockery as m;

class AppInfoGeneratorTest extends PHPUnit_Framework_TestCase
{
	public function tearDown()
	{
		m::close();
	}

	public function makeApp()
	{
		$app = new \Illuminate\Foundation\Application;
		$app->detectEnvironment(function() { return 'production'; });

		$app['config'] = $this->mockConfig();
		$app['request'] = $this->makeRequest();
		$app['router'] = $this->mockRouter();

		return $app;
	}

	public function mockConfig(array $data = array())
	{
		return m::mock('Illuminate\Config\Repository')->shouldReceive('get')->andReturnUsing(function($key, $default = null) use($data) {
			return array_get($data, $key, $default);
		})->getMock();
	}

	public function makeRequest()
	{
		return \Illuminate\Http\Request::create('/foo/bar');
	}

	public function mockRouter()
	{
		$mock = m::mock('Illuminate\Routing\Router');
		$route = new \Illuminate\Routing\Route(['get'], 'foo/bar', ['as' => 'route.name', 'controller' => 'Controller@method']);
		$mock->shouldReceive('current')->andReturn($route)->byDefault();
		return $mock;
	}

	public function makeGenerator($app)
	{
		return new \anlutro\L4SmartErrors\AppInfoGenerator($app);
	}

	/** @test */
	public function generatesStringsProperly()
	{
		$app = $this->makeApp();
		$generator = $this->makeGenerator($app);
		$strings = $generator->getRenderableStrings();

		$this->assertContains('Environment: production', $strings);
		$this->assertContains('URL: http://localhost/foo/bar', $strings);
		$this->assertContains('HTTP method: GET', $strings);
		$this->assertContains('Client IP: 127.0.0.1', $strings);
		$this->assertContains('Route name: route.name', $strings);
		$this->assertContains('Route action: Controller@method', $strings);
		$this->assertContains('User Agent: Symfony/2.X', $strings);
	}

	/** @test */
	public function extraStringsAreAdded()
	{
		$app = $this->makeApp();
		$generator = new StubInfoGenerator($app);
		$strings = $generator->getRenderableStrings();

		$this->assertContains('Foo: bar', $strings);
	}
}

class StubInfoGenerator extends \anlutro\L4SmartErrors\AppInfoGenerator
{
	public function getExtraStrings()
	{
		return ['foo' => 'bar'];
	}
}
