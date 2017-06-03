<?php
namespace anlutrp\L4SmartErrors\Tests;

require dirname(__DIR__).'/vendor/autoload.php';

if (class_exists('PHPUnit_Framework_TestCase')) {
	class_alias('PHPUnit_Framework_TestCase', 'PHPUnit\Framework\TestCase');
	class TestCase extends PHPUnit_Framework_TestCase {}
} elseif (class_exists('PHPUnit\Framework\TestCase')) {
	class_alias('PHPUnit\Framework\TestCase', 'PHPUnit_Framework_TestCase');
	class TestCase extends \PHPUnit\Framework\TestCase {}
}
