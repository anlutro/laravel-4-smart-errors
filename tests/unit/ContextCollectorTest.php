<?php

use anlutro\L4SmartErrors\Log\ContextCollector;
use anlutro\L4SmartErrors\Presenters\InputPresenter;

class ContextCollectorTest extends PHPUnit_Framework_TestCase
{
	/** @test */
	public function input_is_sanitized()
	{
		$app = $this->getApp();
		$input = new InputPresenter(['password' => 'foo'], ['password']);
		$context = (new ContextCollector($app, $input, null, false))
			->getContext();
		$this->assertEquals('HIDDEN', $context['input']['password']);
	}

	public function getApp(array $input = array(), array $session = array())
	{
		$app = new \Illuminate\Foundation\Application();
		$app['env'] = 'testing';
		return $app;
	}
}
