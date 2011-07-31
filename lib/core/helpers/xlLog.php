php<?php
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
 * A logging helper
 * Utilizes the PHP syslog predefined logging constants.
 * See {@link http://php.net/manual/en/function.syslog.php}
 * for more information.
 *
 * @property string $caller
 * @property int $callerLineNumber
 * @property array $validLogLevels
 */
class xlLog extends xlBaseHelper implements xlILog
{
	//********************************************************************************
	//* Private Members
	//********************************************************************************

	/**
	 * Our logging tag
	 */
	const CLASS_LOG_TAG = 'yiixl.core.helpers.xlLog';

	//********************************************************************************
	//* Private Members
	//********************************************************************************

	/**
	 * @var string The class::method or function that made the call
	 */
	protected static $_caller;
	/**
	 * @var integer The line number called from
	 */
	protected static $_callerLineNumber;
	/**
	 * @var array Our valid log levels based on interface definition
	 */
	protected static $_validLogLevels;

	//********************************************************************************
	//* Public Methods
	//********************************************************************************

	/**
	 * Initializer
	 */
	public static function initialize()
	{
		//	Phone home...
		parent::initialize();

		//	Initialize our valid log levels
		self::$_validLogLevels = array();

		$_class = new ReflectionClass( __CLASS__ );
		$_constants = $_class->getConstants();

		foreach ( $_constants as $_name => $_value )
		{
			if ( 0 == strncasecmp( $_name, 'LOG_', 4 ) )
			{
				self::$_validLogLevels[strtolower( substr( $_name, 4 ) )] = $_value;
			}
		}
	}

	/**
	 * Walks the backtrace to find the calling object
	 * @param string $instanceFilter Only return objects of this instance
	 * @return string|null
	 */
	protected static function _getCallerObject( $instanceFilter = null )
	{
		$_stack = debug_backtrace( true );

		for ( $_i = 0, $_count = count( $_stack ); $_i < $_count; $_i++ )
		{
			if ( null !== ( $_caller = XL::o( $_stack[$_i], 'object' ) ) )
			{
				if ( null !== $instanceFilter )
				{
					if ( $_caller instanceof $instanceFilter )
					{
						return $_caller;
					}

					continue;
				}

				return $_caller;
			}
		}

		return null;
	}

	//*******************************************************************************
	//* Helper Methods
	//*******************************************************************************

	/**
	 * Logs a trace message (really a debug)
	 * @param string $message
	 * @param string $category
	 */
	public static function logTrace( $message, $category = null )
	{
		self::_log( $message, self::LOG_DEBUG, $category );
	}

	/**
	 * Logs a trace message (really a debug)
	 * @param string $message
	 * @param string $category
	 */
	public static function trace( $message, $category = null )
	{
		self::_log( $message, self::LOG_DEBUG, $category );
	}

	/**
	 * Logs a message
	 * @param string $message
	 * @param string $category
	 * @return bool
	 */
	public static function logEmergency( $message, $category = null )
	{
		return self::_log( $message, self::LOG_EMERG, $category );
	}

	/**
	 * Logs a message
	 * @param string $message
	 * @param string $category
	 * @return bool
	 */
	public static function emergency( $message, $category = null )
	{
		return self::_log( $message, self::LOG_EMERG, $category );
	}

	/**
	 * Logs a message
	 * @param string $message
	 * @param string $category
	 * @return bool
	 */
	public static function logAlert( $message, $category = null )
	{
		return self::_log( $message, self::LOG_ALERT, $category );
	}

	/**
	 * Logs a message
	 * @param string $message
	 * @param string $category
	 * @return bool
	 */
	public static function alert( $message, $category = null )
	{
		return self::_log( $message, self::LOG_ALERT, $category );
	}

	/**
	 * Logs a message
	 * @param string $message
	 * @param string $category
	 * @return bool
	 */
	public static function logCritical( $message, $category = null )
	{
		return self::_log( $message, self::LOG_CRIT, $category );
	}

	/**
	 * Logs a message
	 * @param string $message
	 * @param string $category
	 * @return bool
	 */
	public static function critical( $message, $category = null )
	{
		return self::_log( $message, self::LOG_CRIT, $category );
	}

	/**
	 * Logs a message
	 * @param string $message
	 * @param string $category
	 * @return bool
	 */
	public static function logError( $message, $category = null )
	{
		return self::_log( $message, self::LOG_ERR, $category );
	}

	/**
	 * Logs a message
	 * @param string $message
	 * @param string $category
	 * @return bool
	 */
	public static function error( $message, $category = null )
	{
		return self::_log( $message, self::LOG_ERR, $category );
	}

	/**
	 * Logs a message
	 * @param string $message
	 * @param string $category
	 * @return bool
	 */
	public static function logWarning( $message, $category = null )
	{
		return self::_log( $message, self::LOG_WARNING, $category );
	}

	/**
	 * Logs a message
	 * @param string $message
	 * @param string $category
	 * @return bool
	 */
	public static function warning( $message, $category = null )
	{
		return self::_log( $message, self::LOG_WARNING, $category );
	}

	/**
	 * Logs a message
	 * @param string $message
	 * @param string $category
	 * @return bool
	 */
	public static function logNotice( $message, $category = null )
	{
		return self::_log( $message, self::LOG_NOTICE, $category );
	}

	/**
	 * Logs a message
	 * @param string $message
	 * @param string $category
	 * @return bool
	 */
	public static function notice( $message, $category = null )
	{
		return self::_log( $message, self::LOG_NOTICE, $category );
	}

	/**
	 * Logs a message
	 * @param string $message
	 * @param string $category
	 * @return bool
	 */
	public static function logInfo( $message, $category = null )
	{
		return self::_log( $message, self::LOG_INFO, $category );
	}

	/**
	 * Logs a message
	 * @param string $message
	 * @param string $category
	 * @return bool
	 */
	public static function info( $message, $category = null )
	{
		return self::_log( $message, self::LOG_INFO, $category );
	}

	/**
	 * Logs a message
	 * @param string $message
	 * @param string $category
	 * @return bool
	 */
	public static function logDebug( $message, $category = null )
	{
		return self::_log( $message, self::LOG_DEBUG, $category );
	}

	/**
	 * Logs a message
	 * @param string $message
	 * @param string $category
	 * @return bool
	 */
	public static function debug( $message, $category = null )
	{
		return self::_log( $message, self::LOG_DEBUG, $category );
	}

	/**
	 * Logs a message
	 * @param string $message
	 * @param string $category
	 * @return bool
	 */
	public static function logUser( $message, $category = null )
	{
		return self::_log( $message, self::LOG_USER, $category );
	}

	/**
	 * Logs a message
	 * @param string $message
	 * @param string $category
	 * @return bool
	 */
	public static function user( $message, $category = null )
	{
		return self::_log( $message, self::LOG_USER, $category );
	}

	/**
	 * Logs a message
	 * @param string $message
	 * @param string $category
	 * @return bool
	 */
	public static function logAuth( $message, $category = null )
	{
		return self::_log( $message, self::LOG_AUTH, $category );
	}

	/**
	 * Logs a message
	 * @param string $message
	 * @param string $category
	 * @return bool
	 */
	public static function auth( $message, $category = null )
	{
		return self::_log( $message, self::LOG_AUTH, $category );
	}

	/**
	 * Logs a message
	 * @param string $message
	 * @param string $category
	 * @return bool
	 */
	public static function logSyslog( $message, $category = null )
	{
		return self::_log( $message, self::LOG_SYSLOG, $category );
	}

	/**
	 * Logs a message
	 * @param string $message
	 * @param string $category
	 * @return bool
	 */
	public static function syslog( $message, $category = null )
	{
		return self::_log( $message, self::LOG_SYSLOG, $category );
	}

	/**
	 * Logs a message
	 * @param string $message
	 * @param string $category
	 * @return bool
	 */
	public static function logAuthPriv( $message, $category = null )
	{
		return self::_log( $message, self::LOG_AUTHPRIV, $category );
	}

	/**
	 * Logs a message
	 * @param string $message
	 * @param string $category
	 * @return bool
	 */
	public static function authPriv( $message, $category = null )
	{
		return self::_log( $message, self::LOG_AUTHPRIV, $category );
	}

	//*******************************************************************************
	//* Private Methods
	//*******************************************************************************

	/**
	 * Polymorphic logger
	 * @param string $message
	 * @param int $level <p>
	 * level indicates tje severity of the item being logged. Possible values are:
	 * <table>
	 * Logging Levels (in descending order)
	 * <tr valign="top">
	 * <td>Constant</td>
	 * <td>Description</td>
	 * </tr>
	 * <tr valign="top">
	 * <td>LOG_EMERG</td>
	 * <td>system is unusable</td>
	 * </tr>
	 * <tr valign="top">
	 * <td>LOG_ALERT</td>
	 * <td>action must be taken immediately</td>
	 * </tr>
	 * <tr valign="top">
	 * <td>LOG_CRIT</td>
	 * <td>critical conditions</td>
	 * </tr>
	 * <tr valign="top">
	 * <td>LOG_ERR</td>
	 * <td>error conditions</td>
	 * </tr>
	 * <tr valign="top">
	 * <td>LOG_WARNING</td>
	 * <td>warning conditions</td>
	 * </tr>
	 * <tr valign="top">
	 * <td>LOG_NOTICE</td>
	 * <td>normal, but significant, condition</td>
	 * </tr>
	 * <tr valign="top">
	 * <td>LOG_INFO</td>
	 * <td>informational message</td>
	 * </tr>
	 * <tr valign="top">
	 * <td>LOG_DEBUG</td>
	 * <td>debug-level message</td>
	 * </tr>
	 * </table>
	 * </p>
	 * @param string $category
	 * @return bool Returns true or throws an exception
	 */
	protected static function _log( $message, $level = LOG_INFO, $category = null )
	{
		try
		{
			Yii::log( $message, $level, $category );
		}
		catch ( Exception $_ex )
		{
			throw new xlLogException( $_ex->getMessage(), $_ex->getCode() );
		}

		return true;
	}
}