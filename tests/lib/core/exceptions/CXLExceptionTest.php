<?php

require_once dirname( __FILE__ ) . '/../../../../lib/core/exceptions/CXLException.php';

/**
 * Test class for CXLException.
 * Generated by PHPUnit on 2011-03-06 at 00:32:15.
 */
class CXLExceptionTest extends PHPUnit_Framework_TestCase
{

	/**
	 * @var CXLException
	 */
	protected $object;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 */
	protected function setUp()
	{
		$this->object = new CXLException;
	}

	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 */
	protected function tearDown()
	{

	}

}

?>