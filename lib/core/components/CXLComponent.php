<?php
/**
 * This file is part of the YiiXL package.
 *
 * @copyright Copyright (c) 2009-2011 Pogostick, LLC.
 * @link http://www.pogostick.com Pogostick, LLC.
 * @license http://www.pogostick.com/licensing
 * @author Jerry Ablan <jablan@pogostick.com>
 *
 * @since v1.0.0
 *
 * @package yiixl
 * @subpackage core.components
 *
 * @filesource
 */

//	Requirements
require_once dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'interfaces.php';

/**
 * CXLComponent class
 * This is the base class for all YiiXL library objects. It extends the base
 * functionality of the Yii Framework without replacing core code.
 *
 * The two enhancements are:
 * 1. Introduces a new propery called $debugLevel that allows fine-tuning of debug and tracing
 * 2. Has a constructor that accepts an array of configuration parameters.
 *
 * @property integer $debugLevel A user-defined debugging level
 * @property-read array $options The options passed to this object during construction
 */
class CXLComponent extends CApplicationComponent implements IXLComponent
{
	//********************************************************************************
	//* Constants
	//********************************************************************************

	/**
	 * Our logging tag
	 */
	const	CLASS_LOG_TAG = 'yiixl.core.components.CXLComponent';

	//********************************************************************************
	//* Properties
	//********************************************************************************

	/**
	 * @var integer $_debugLevel User-defined debug flag. false = OFF, anything else is up to you.
	 */
	protected $_debugLevel = false;

	/**
	 * @var array $_options Our configuration options
	 */
	protected $_options;

	/**
	 * @var array $_behaviors Attached behaviors. We have to copy here because Yii is private instead of protected
	 */
	protected $_behaviorCache;

	/**
	 * @var array $_behaviorMethods Imported attached behavior methods
	 */
	protected $_behaviorMethods;

	//********************************************************************************
	//* Property Accessors
	//********************************************************************************

	/**
	 * Gets the debug level
	 * @return integer
	 */
	public function getDebugLevel()
	{
		return $this->_debugLevel;
	}

	/**
	 * Sets the debug level
	 * @param integer The new debug level
	 */
	public function setDebugLevel( $value = false )
	{
		$this->_debugLevel = $value;
	}

	/**
	 * Gets configuration options
	 * @return array
	 */
	public function getOptions()
	{
		return $this->_options;
	}

	//********************************************************************************
	//* Public Methods
	//********************************************************************************

	/**
	 * Constructs a component.
	 * All components accept an array of configuration options. These options are placed into accessible
	 * members (via "setter"). The entire array is stored in the member {@see IXLComponent::options}.
	 */
	public function __construct( $options = array() )
	{
		//	Set any properties via standard config array
		$this->_loadConfiguration( $options );
	}

	/**
	 * Gets a single configuration option
	 * @return mixed
	 */
	public function getOption( $key, $defaultValue = null, $unsetAfter = false )
	{
		return YiiXL::o( $this->_options, $key, $defaultValue, $unsetAfter );
	}

	/**
	 * Attaches a behavior to this component.
	 * Calls the Yii implementation of this method, then injects the
	 * behaviors methods into our class.
	 * @param string $name The behavior's name. It should uniquely identify this behavior.
	 * @param mixed $behavior the behavior configuration. This is passed as the first
	 * parameter to {@link YiiBase::createComponent} to create the behavior object.
	 * @return IBehavior the behavior object
	 */
	public function attachBehavior( $name, $behavior )
	{
		if ( null !== ( $_behavior = parent::attachBehavior( $name, $behavior ) ) )
		{
			//	Import the behavior methods
			$_class = get_class( $_behavior );
			$_methods = get_class_methods( $_behavior );

			array_push(
				$this->_behaviors,
				array(
					$_class,
					$_behavior
				)
			);

			foreach ( $_methods as $_method )
				$this->_behaviorMethods[ $_method] = &$_behavior;
		}
	}

	/**
	 * Detaches a behavior from the component.
	 * The behavior's {@link IBehavior::detach} method will be invoked.
	 * @param string $name the behavior's name. It uniquely identifies the behavior.
	 * @return IBehavior the detached behavior. Null if the behavior does not exist.
	 * @since 1.0.2
	 * @todo Implement un-importing methods
	 */
	public function detachBehavior( $name )
	{
		if ( null !== ( $_behavior = parent::detachBehavior( $name ) ) )
		{
			//	Unimport...
		}
	}

	/**
	 *
	 * @param string $name
	 * @param array $parameters
	 * @return mixed
	 */
	public function __call( $name, $parameters )
	{
		//	Make sure the function exists
		if ( isset( $this->_behaviorMethods, $this->_behaviorMethods[$name] ) || isset( $this->_behaviorMethods['__call'] ) )
		{
			//	Throw myself at the end of the arguments
			$parameters[] = $this;

			//	And call the method
			return call_user_func_array(
				array(
					$this->_behavirorMethods[$name],
					$name
				),
				$parameters
			);
		}

		//	Otherwise let Yii deal with it...
		return parent::__call( $name, $parameters );
	}

	//********************************************************************************
	//* Private Methods
	//********************************************************************************

	/**
	 * Loads an array into properties if they exist.
	 * @param array $options
	 */
	protected function _loadConfiguration( $options = array(), $overwriteExisting = true )
	{
		XL::logDebug( 'Loading configuration options: ' . print_r( $options, true ), self::CLASS_LOG_TAG );

		//	Make a copy for posterity
		if ( $overwriteExisting || empty( $this->_options ) )
			$this->_options = $options;
		else
			$this->_options = array_merge( $this->_options, $options );

		//	Try to set each one
		try
		{
			foreach ( $options as $_key => $_value )
			{
				try
				{
					//	See if __set has a better time with this...
					if ( method_exists( $this, 'set' . $_key ) )
						$this->{'set' . $_key}( $_value );
					else if ( property_exists( $this, $_key ) )
						$this->{$_key} = $_key;
				}
				catch ( Exception $_ex )
				{
					//	Completely ignore errors...
				}
			}
		}
		catch ( Exception $_ex )
		{
			XL::logError( 'Error while loading configuration options: ' . $_ex->getMessage(), self::CLASS_LOG_TAG );
		}
	}

}