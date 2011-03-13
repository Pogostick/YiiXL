<?php
/**
 * This file is part of the YiiXL package.
 *
 * @copyright Copyright (c) 2009-2011 Pogostick, LLC.
 * @link http://www.pogostick.com Pogostick, LLC.
 * @license http://www.pogostick.com/licensing
 *
 * @package yiixl
 * @subpackage core.helpers
 *
 * @author 		Jerry Ablan <jablan@pogostick.com>
 * @since 		v1.0.0
 *
 * @filesource
 */

/**
 * Provides abstracted options setting functionality
 */
class CXLOptions extends CXLBaseHelper implements IXLShifter, IXLUtilityHelper
{
	//********************************************************************************
	//* Constants
	//********************************************************************************

	/**
	 * Our logging tag
	 */
	const	CLASS_LOG_TAG = 'yiixl.core.helpers.CXLOptions';

	//********************************************************************************
	//* Public Methods
	//********************************************************************************

	/**
	 * Sets options of an object
	 * @param IXLComponent $object
	 * @param array $options
	 */
	public static function setOptions( IXLComponent $object, array $options )
	{
		if ( ! is_object( $object ) )
			return;

		foreach ( $options as $_key => $_value )
		{
			$_method = 'set' . self::_cleanKey( $_key );

			if ( is_callable( array( $object, $_method ) ) )
				$object->$_method( $_value );
		}
	}

	/**
	 * Sets the options in $object
	 * @param IXLComponent  $object
	 * @param array $options 
	 */
	public static function setConstructorOptions( IXLComponent $object, $options )
	{
		if ( $options instanceof CXLConfig )
			$options = $options->toArray();

		if ( is_array( $options ) )
			self::setOptions( $object, $options );
	}

	/**
	 * Loads an array into properties if they exist.
	 * @param IXLComponent $object
	 * @param array $objectOptions
	 * @param array $options
	 * @param boolean $overwriteExisting 
	 * @return boolean
	 */
	public static function loadConfiguration( IXLComponent $object, $options = array(), $overwriteExisting = true )
	{
		XL::logDebug( 'Loading configuration options: ' . print_r( $options, true ), $object::CLASS_LOG_TAG );

		//	Get a handle on the options to modify
		$_objectOptions = $object->getOptions();
		
		//	Make a copy for posterity
		if ( $overwriteExisting || empty( $_objectOptions ) )
			$_objectOptions = $options;
		else
			$_objectOptions = array_merge( $_objectOptions, $options );
		
		$object->loadOptions( $_objectOptions );

		//	Try to set each one
		try
		{
			foreach ( $options as $_key => $_value )
			{
				try
				{
					//	See if __set has a better time with this...
					if ( is_callable( array( $object, 'set' . $_key ) ) )
						$object->{'set' . $_key}( $_value );
					else if ( property_exists( $object, $_key ) )
						$object->{$_key} = $_key;
				}
				catch ( Exception $_ex )
				{
					//	Completely ignore errors...
				}
			}
		}
		catch ( Exception $_ex )
		{
			XL::logError( 'Exception while loading configuration options: ' . $_ex->getMessage(), $object::CLASS_LOG_TAG );
			return false;
		}
		
		return true;
	}

	//********************************************************************************
	//* Private Methods
	//********************************************************************************

	/**
	 * Cleans up key names
	 * @param string $key
	 * @return string
	 */
	protected static function _cleanKey( $key )
	{
		return str_replace( 
			' ', 
			'', 
			ucwords( 
				str_replace( 
					'_', 
					'', 
					strtolower( $key )
				)
			)
		);
	}

}
