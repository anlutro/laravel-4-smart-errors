<?php

class QueryLogPresenterTest extends PHPUnit_Framework_TestCase
{
	public function makePresenter($log)
	{
		return new anlutro\L4SmartErrors\Presenters\QueryLogPresenter($log);
	}

	/**
	 * @dataProvider getQueryLogData
	 */
	public function testContainsCorrectInformation($log, $expected)
	{
		$presenter = $this->makePresenter($log);
		$str = $presenter->renderPlain();
		foreach ($expected as $value) {
			$this->assertContains($value, $str);
		}
	}

	public function getQueryLogData()
	{
		return array(array(
		array( // actual query log
			array(
				'query' => 'select * from foo where id = ?',
				'bindings' => array(1),
				'time' => 234,
			), array(
				'query' => 'insert into foo (bar) values (?)',
				'bindings' => array('baz'),
				'time' => 345,
			)
		),
		array( // expected strings to find
			'select * from foo where id = ?',
			'insert into foo (bar) values (?)',
			'baz',
			'234',
			'345',
		)
		));
	}
}
