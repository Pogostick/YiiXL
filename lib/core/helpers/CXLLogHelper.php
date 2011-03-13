php<?php
/**
 * This file is part of the YiiXL package.
 *
 * @copyright Copyright (c) 2009-2011 Pogostick, LLC.
 * @link http://www.pogostick.com Pogostick, LLC.
 * @license http://www.pogostick.com/licensing
 * @author Jerry Ablan <jablan@pogostick.com>
 * @since v1.0.0
 * @package yiixl
 * @subpackage behaviors
 */

/**
 * A logging helper
 * Utilizes the PHP syslog predefined logging constants.
 * See {@link http://php.net/manual/en/function.syslog.php}
 * for more information.
 */
class CXLLogHelper extends CXLBaseHelper implements IXLLogger
{
	//********************************************************************************
	//* Private Members
	//********************************************************************************

	/**
	 * Our logging tag
	 */
	const CLASS_LOG_TAG = 'yiixl.core.helpers.CXLLogHelper';

	//********************************************************************************
	//* Private Members
	//********************************************************************************

	/**
	 * @var string $_caller The class::method or function that made the call
	 */
	protected static $_caller;

	/**
	 * @var integer $_callerLineNumber The line number called from
	 */
	protected static $_callerLineNumber;

	/**
	 * @var array $_validLogLevels Our valid log levels based on interface definition
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
		
		//	Initialize our valid log lvles
		self::$_validLogLevels = array();

		$_class = new ReflectionClass( __CLASS__ );
		$_constants = $_class->getConstants();

		foreach ( $_constants as $_name => $_value )
		{
			if ( 0 == strncasecmp( $_name, 'LOG_', 4 ) )
				self::$_validLogLevels[strtolower( substr( $_name, 4 ) )] = $_value;
		}
	}

	//*******************************************************************************
	//* Helper Methods
	//*******************************************************************************

	/**
	 * Logs a trace message (really a debug)
	 * @param string $message
	 * @param string $category
	 */
	public function logTrace( $message, $category = null )
	{
		self::_log( $message, self::LOG_DEBUG, $category );
	}
	
	/**
	 * Logs a message
	 * @param string $message
	 * @param string $category
	 */
	public function logEmergency( $message, $category = null )
	{
		return self::_log( $message, self::LOG_EMERG, $category );
	}
	
	/**
	 * Logs a message
	 * @param string $message
	 * @param string $category
	 */
	public function logAlert( $message, $category = null )
	{
		return self::_log( $message, self::LOG_ALERT, $category );
	}
	
	/**
	 * Logs a message
	 * @param string $message
	 * @param string $category
	 */
	public function logCritical( $message, $category = null )
	{
		return self::_log( $message, self::LOG_CRITICAL, $category );
	}
	
	/**
	 * Logs a message
	 * @param string $message
	 * @param string $category
	 */
	public function logError( $message, $category = null )
	{
		return self::_log( $message, self::LOG_ERROR, $category );
	}
	
	/**
	 * Logs a message
	 * @param string $message
	 * @param string $category
	 */
	public function logWarning( $message, $category = null )
	{
		return self::_log( $message, self::LOG_WARNING, $category );
	}
	
	/**
	 * Logs a message
	 * @param string $message
	 * @param string $category
	 */
	public function logNotice( $message, $category = null )
	{
		return self::_log( $message, self::LOG_NOTICE, $category );
	}
	
	/**
	 * Logs a message
	 * @param string $message
	 * @param string $category
	 */
	public function logInfo( $message, $category = null )
	{
		return self::_log( $message, self::LOG_INFO, $category );
	}
	
	/**
	 * Logs a message
	 * @param string $message
	 * @param string $category
	 */
	public function logDebug( $message, $category = null )
	{
		return self::_log( $message, self::LOG_DEBUG, $category );
	}
	
	/**
	 * Logs a message
	 * @param string $message
	 * @param string $category
	 */
	public function logUser( $message, $category = null )
	{
		return self::_log( $message, self::LOG_USER, $category );
	}
	
	/**
	 * Logs a message
	 * @param string $message
	 * @param string $category
	 */
	public function logAuth( $message, $category = null )
	{
		return self::_log( $message, self::LOG_AUTH, $category );
	}
	
	/**
	 * Logs a message
	 * @param string $message
	 * @param string $category
	 */
	public function logSyslog( $message, $category = null )
	{
		return self::_log( $message, self::LOG_SYSLOG, $category );
	}
	
	/**
	 * Logs a message
	 * @param string $message
	 * @param string $category
	 */
	public function logAuthPriv( $message, $category = null )
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
	 * @return bool Returns true on success or false on failure.
	 */
	protected function _log( $message, $level = LOG_INFO, $category = null )
	{
		return Yii::log( $message, $level, $category );
	}
}