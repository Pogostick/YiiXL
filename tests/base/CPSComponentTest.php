<?php

require_once dirname( __FILE__ ) . '/../../../../lib/base/CPSComponent.php';

/**
 * Test class for CPSComponent.
 * Generated by PHPUnit on 2011-03-05 at 13:58:23.
 */
class CPSComponentTest extends PHPUnit_Framework_TestCase
{

	/**
	 * @var CPSComponent
	 */
	protected $object;

	/**
	 * @var array
	 */
	protected $_testOptions = array(
		'option.1' => 'option.1.value',
		'option.2' => 'option.2.value',
	);

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 */
	protected function setUp()
	{
		$this->object = new CPSComponent( $this->_testOptions );
	}

	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 */
	protected function tearDown()
	{

	}

	/**
	 * @todo Implement testGetDebugLevel().
	 */
	public function testGetDebugLevel()
	{
		$this->object->setDebugLevel( E_ALL & ~E_NOTICE );
		$this->assertEquals( E_ALL & ~E_NOTICE, $this->object->getDebugLevel() );
	}

	/**
	 * @todo Implement testSetDebugLevel().
	 */
	public function testSetDebugLevel()
	{
		$this->object->setDebugLevel( false );
		$this->assertEquals( false, $this->object->getDebugLevel() );
	}

	/**
	 * @todo Implement testGetOptions().
	 */
	public function testGetOptions()
	{
		$this->assertEquals( $this->_testOptions, $this->object->getOptions() );
	}

	/**
	 * @todo Implement testGetOption().
	 */
	public function testGetOption()
	{
		$this->assertEquals( 'option.1.value', $this->object->getOption( 'option.1' ) );

		//	Get option #2, but unset after
		$this->assertEquals( 'option.2.value', $this->object->getOption( 'option.2', null, true ) );

		echo 'option.2 = ' . print_r( $this->object->getOptions(), true );

		//	Now re-pull, should be null
		$_value = $this->object->getOption( 'option.2' );
		$this->assertNull( $_value );
	}
}
