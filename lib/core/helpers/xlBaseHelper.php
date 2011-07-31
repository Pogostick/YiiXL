<?php
/**
 * This file is part of YiiXL
 * Copyright (c) 2009-2011, Pogostick, LLC. All rights reserved.
 *
 * @link http://www.pogostick.com Pogostick, LLC.
 * @license http://www.pogostick.com/licensing
 * @author Jerry Ablan <yiixl@pogostick.com>
 *
 * @since v1.0.0
 *
 * @package yiixl
 * @subpackage core.helpers
 *
 * @filesource
 */
/**
 * The mother of all helper classes
 */
class xlBaseHelper extends xlComponent implements xlIHelper
{
	//********************************************************************************
	//* Constants
	//********************************************************************************

	/**
	 * @const string Our logging tag
	 */
	const
		CLASS_LOG_TAG = 'yiixl.core.helpers.xlBaseHelper';

	//********************************************************************************
	//* Public Methods
	//********************************************************************************
	
	/**
	 * Initializes the helper
	 */
	public static function initialize()
	{
	}

	/**
	 * Given a variable, retrieves prior value before setting to new value
	 * @static
	 * @param mixed $variable
	 * @param mixed $value
	 * @return mixed
	 */
	protected static function _swapValue( &$variable, $value )
	{
		$_oldValue = $variable;
		$variable = $value;
		return $_oldValue;
	}

	/**
	 * Down and dirty logger
	 * @param string $message
	 * @param string $destination The file name to output to
	 */
	protected function _rawLog( $message, $destination = null )
	{
		if ( $this->_enableLogging )
		{
			if ( null === $destination )
			{
				$destination = './' . strtolower( get_class() ) . '.php.raw.log';
			}

			error_log( date( 'Y-m-d H:i:s' ) . ' :: ' . $message . PHP_EOL, 3, $destination );
		}
	}

}