<?php
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
class CXLLogHelper implements IXLHelper, IXLLogger
{
	//********************************************************************************
	//* Private Members
	//********************************************************************************

	/**
	 * Our logging tag
	 */
	const CLASS_LOG_TAG = 'yiixl.core.helpers';

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
	public static function init()
	{
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

	/**
	 * Calls a static method in classPath if not found here. Allows you to extend this object
	 * at runtime with additional helpers.
	 *
	 * Only available in PHP 5.3+
	 *
	 * @param string $method
	 * @param array $parameters
	 * @return mixed
	 */
	public static function __callStatic( $method, $parameters )
	{
		//	Grab any log requests and redirect...
		if ( 0 == strncasecmp( $method, 'log', 3 ) )
		{
			//	The level
			$_level = trim( strtolower( substr( $method, 3 ) ) );

			if ( ! empty( $_level ) && in_array( $_level, array_keys( self::$_validLogLevels ) ) )
			{
				$_message = array_shift( $parameters );

				if ( null === $_category = array_shift( $parameters ) )
					$_category = self::CLASS_LOG_TAG;

				return self::_log( $_message, $_level, $_category );
			}
		}
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
	 * @param string $message <p>
	 * The message to log, except that the two characters
	 * %m will be replaced by the error message string
	 * (strerror) corresponding to the present value of
	 * errno.
	 * </p>
	 * @return bool Returns true on success or false on failure.
	 */
	protected function _log( $message, $level = LOG_INFO, $category = null )
	{
		return Yii::log( $message, $level, $category );
	}
}