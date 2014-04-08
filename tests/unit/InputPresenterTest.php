<?php

class InputPresenterTest extends PHPUnit_Framework_TestCase
{
	public function testStringContainsKeyAndValue()
	{
		$presenter = $this->makePresenter(array('foo' => 'bar'));
		$str = $presenter->render();
		$this->assertInternalType('string', $str);
		$this->assertContains('foo', $str);
		$this->assertContains('bar', $str);
	}

	public function testStringDoesNotContainPreTagWhenHtmlFalse()
	{
		$presenter = $this->makePresenter(array('foo' => 'bar'));
		$str = $presenter->render();
		$this->assertNotContains('<pre', $str);
	}

	public function testStringContainsPreTagWhenHtmlTrue()
	{
		$presenter = $this->makePresenter(array('foo' => 'bar'));
		$str = $presenter->renderHtml();
		$this->assertContains('<pre', $str);
	}

	public function makePresenter(array $input)
	{
		return new anlutro\L4SmartErrors\InputPresenter($input);
	}
}
