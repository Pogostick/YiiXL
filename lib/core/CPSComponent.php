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
 * @subpackage core
 *
 * @filesource
 */

//	Our namespace
//namespace yiixl\core;

/**
 * CPSComponent class
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
class CPSComponent extends CApplicationComponent implements IPSComponent
{
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
	 * members (via "setter"). The entire array is stored in the member {@see CPSComponent::options}.
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
		return PS::o( $this->_options, $key, $defaultValue, $unsetAfter );
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
			Yii::log( 'Error while loading configuration options: ' . $_ex->getMessage(), 'error', 'yiixl.base' );
		}
	}

}