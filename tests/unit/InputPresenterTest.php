<?php

class InputPresenterTest extends PHPUnit_Framework_TestCase
{
	public function testStringContainsKeyAndValue()
	{
		$presenter = $this->makePresenter(array('foo' => 'bar'));
		$str = $presenter->renderPlain();
		$this->assertInternalType('string', $str);
		$this->assertContains('foo', $str);
		$this->assertContains('bar', $str);
	}

	public function testStringDoesNotContainPreTagWhenHtmlFalse()
	{
		$presenter = $this->makePresenter(array('foo' => 'bar'));
		$str = $presenter->renderPlain();
		$this->assertNotContains('<pre', $str);
	}

	public function testStringContainsPreTagWhenHtmlTrue()
	{
		$presenter = $this->makePresenter(array('foo' => 'bar'));
		$str = $presenter->renderHtml();
		$this->assertContains('<pre', $str);
	}

	public function testPasswordsAreSanitized()
	{
		$presenter = $this->makePresenter(array('password' => 'foo', 'password_confirmation' => 'foo'));
		$str = $presenter->renderPlain();
		$this->assertNotContains('foo', $str);
		$this->assertContains('HIDDEN', $str);
		$str = $presenter->renderHtml();
		$this->assertNotContains('foo', $str);
		$this->assertContains('HIDDEN', $str);
		$str = $presenter->renderCompact();
		$this->assertNotContains('foo', $str);
		$this->assertContains('HIDDEN', $str);
	}

	public function makePresenter(array $input)
	{
		return new anlutro\L4SmartErrors\Presenters\InputPresenter($input, ["password"]);
	}
}
