<?php
require_once dirname( __FILE__ ) . '/../../../../../lib/core/components/xlApplicationComponent.php';
require_once dirname( __FILE__ ) . '/../../../../../lib/core/components/config/xlConfig.php';

/**
 * Test class for xlConfig.
 * Generated by PHPUnit on 2011-03-11 at 19:05:48.
 */
class CXLConfigTest extends CTestCase
{
	/**
	 * @var CXLConfig $_object
	 */
	protected $_object;

	/**
	 * @var array
	 */
	protected $_testOptions = array(
		'option.0' => 'option.0.value',
		'option.1' => 'option.1.value',
		'option.2' => 'option.2.value',
		'option.3' => 'option.3.value',
		'option.4' => 'option.4.value',
		'option.5' => 'option.5.value',
		'option.6' => 'option.6.value',
		'option.7' => 'option.7.value',
		'option.8' => 'option.8.value',
		'option.9' => 'option.9.value',
		'option.array' => array(
			'suboption.0' => 'suboption.0.value',
			'suboption.1' => 'suboption.1.value',
			'suboption.2' => 'suboption.2.value',
			'suboption.3' => 'suboption.3.value',
			'suboption.4' => 'suboption.4.value',
			'suboption.5' => 'suboption.5.value',
			'suboption.6' => 'suboption.6.value',
			'suboption.7' => 'suboption.7.value',
			'suboption.8' => 'suboption.8.value',
			'suboption.9' => 'suboption.9.value',
		),
	);

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 */
	protected function setUp()
	{
		xdebug_break();
		$this->_object = new xlConfig( $this->_testOptions );
	}

	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 */
	protected function tearDown()
	{
	}

	/**
	 * @todo Implement testGetOption().
	 */
	public function testGetOption()
	{
		$_value = $this->_object->getOption( 'option.4' );
		$this->assertEquals( 'option.4.value', $_value );
	}

	/**
	 * @todo Implement testToArray().
	 */
	public function testToArray()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * @todo Implement testMerge().
	 */
	public function testMerge()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * @todo Implement testJsonEncode().
	 */
	public function testJsonEncode()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * @todo Implement testJsonDecode().
	 */
	public function testJsonDecode()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * @todo Implement testCount().
	 */
	public function testCount()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * @todo Implement testCurrent().
	 */
	public function testCurrent()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * @todo Implement testKey().
	 */
	public function testKey()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * @todo Implement testNext().
	 */
	public function testNext()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * @todo Implement testRewind().
	 */
	public function testRewind()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * @todo Implement testValid().
	 */
	public function testValid()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * @todo Implement test__get().
	 */
	public function test__get()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * @todo Implement test__set().
	 */
	public function test__set()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * @todo Implement test__clone().
	 */
	public function test__clone()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * @todo Implement test__isset().
	 */
	public function test__isset()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * @todo Implement test__unset().
	 */
	public function test__unset()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * @todo Implement testSetReadOnly().
	 */
	public function testSetReadOnly()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * @todo Implement testGetReadOnly().
	 */
	public function testGetReadOnly()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}
	
	public static function main() 
	{
		$_suite = new PHPUnit_Framework_TestSuite( __CLASS__ );
		PHPUnit_TextUI_TestRunner::run( $_suite );
	}
}

if ( ! defined( 'PHPUnit_MAIN_METHOD' ) )
    CXLConfigTest::main();

?>
