<?php
/**
 * YiiXL(tm) : The Yii Extension Library of Doom! (http://github.com/Pogostick/yiixl/)
 * Copyright 2009-2011, Pogostick, LLC. (http://www.pogostick.com/)
 *
 * Dual licensed under the MIT License and the GNU General Public License (GPL) Version 2.
 * See {@link http://www.pogostick.com/licensing/} for complete information.
 *
 * @copyright		Copyright 2009-2011, Pogostick, LLC. (http://www.pogostick.com/)
 * @link			https://github.com/Pogostick/yiixl/ The Yii Extension Library of Doom!
 * @license			http://www.pogostick.com/licensing
 * @author			Jerry Ablan <yiixl@pogostick.com>
 *
 * @package			yiixl
 * @category		yiixl
 * @since			v1.0.0
 *
 * @brief 			This file contains all the YiiXL interfaces
 *
 * @filesource
 */

//*************************************************************************
//*	Foundation Interfaces
//*************************************************************************

/**
 * This interface defines constants for, and identifies an object as, a YiiXL component
 */
interface xlIComponent
{
}

/**
 * This interface is for components that support debugging levels
 */
interface xlIDebuggable extends xlILog
{
	/**
	 * Gets the debug level
	 * @return integer
	 */
	public function getDebugLevel();

	/**
	 * Sets the debug level
	 * @param integer $value
	 * @return integer The previous value
	 */
	public function setDebugLevel( $value = xlILog::LOG_INFO );

}

/**
 * This interface does nothing other than indicate that an exception is
 * part of the YiiXL package.
 */
interface xlIException extends xlIComponent
{
}

/**
 * This identifies an object as a streamable object
 */
interface xlIStreamable extends xlIComponent, xlIDebuggable
{
}

/**
 * This interface defines an object as a provider of constant values
 */
interface xlIConstantProvider  extends xlIComponent
{
}

/**
 * Provides constants for controller classes
 */
interface xlIController extends xlIDebuggable, xlIForm
{
}

//*************************************************************************
//*	Constant Providers
//*************************************************************************

/**
 * This interface defines an object as an access control object
 */
interface xlIAccess extends xlIConstantProvider
{
	/**
	 * @const integer The available access levels for access filtering/control
	 */
	const
		ACCESS_TO_NONE = -1,
		ACCESS_TO_ANY = 0,
		ACCESS_TO_GUEST = 1,
		ACCESS_TO_AUTH = 2,
		ACCESS_TO_ADMIN = 3,
		ACCESS_TO_ADMIN_LEVEL_0 = 3,
		ACCESS_TO_ADMIN_LEVEL_1 = 4,
		ACCESS_TO_ADMIN_LEVEL_2 = 5
	;
}

/**
 * Constant provider of where a pager can be placed in relation to a grid/data view
 */
interface xlIPagerLocation extends xlIConstantProvider
{
	/**
	* @const integer Where a pager can be placed in relation to a grid view
	*/
	const
		PL_TOP_LEFT		= 0,
		PL_TOP_RIGHT	= 1,
		PL_BOTTOM_LEFT	= 2,
		PL_BOTTOM_RIGHT	= 3
	;
}

/**
 * The various predefined actions that can be used on an xlForm
 */
interface xlIFormAction extends xlIConstantProvider
{
	/***
	* @const integer The predefined action types for xlForm
	*/
	const
		ACTION_NONE 	= 0,
		ACTION_CREATE 	= 1,
		ACTION_VIEW 	= 2,
		ACTION_EDIT 	= 3,
		ACTION_SAVE 	= 4,
		ACTION_DELETE 	= 5,
		ACTION_ADMIN 	= 6,
		ACTION_LOCK 	= 7,
		ACTION_UNLOCK 	= 8,
		ACTION_PREVIEW 	= 996,
		ACTION_RETURN 	= 997,
		ACTION_CANCEL 	= 998,
		ACTION_GENERIC 	= 999
	;

}

/**
 * Constants for forms
 */
interface xlIForm extends xlIPagerLocation, xlIFormAction
{
	/**
	* @const integer The number of items to display per page
	*/
	const
		PAGE_SIZE = 10
	;

	/**
	 * @const string The name of our command form field
	 */
	const
		COMMAND_FIELD_NAME = '__yxl'
	;

	/**
	 * @const string Standard search text for rendering
	 */
	const
		SEARCH_HELP_TEXT =<<<HTML
You may optionally enter a comparison operator (<strong>&lt;</strong>, <strong>&lt;=</strong>, <strong>&gt;</strong>, <strong>&gt;=</strong>, <strong>&lt;&gt;</strong>or <strong>=</strong>) at the beginning of each
search value to specify how the comparison should be done.
HTML;
}

/**
 * This interface defines a logger
 */
interface xlILog extends xlIConstantProvider
{
	/**
	 * Logging Constants
	 */
	const
		LOG_EMERG = 0,
		LOG_ALERT = 1,
		LOG_CRIT = 2,
		LOG_ERR = 3,
		LOG_WARNING = 4,
		LOG_NOTICE = 5,
		LOG_INFO = 6,
		LOG_DEBUG = 7,
		LOG_USER = 8,
		LOG_AUTH = 32,
		LOG_SYSLOG = 40,
		LOG_AUTHPRIV = 80;
}

/**
 * This interface defines the base transformation formats
 */
interface xlITransform extends xlIConstantProvider
{
	/**
	* @const int Standard transformation formats
	*/
	const
		OF_JSON 		= 0,		//	JSON
		OF_HTTP 		= 1,		//	HTTP
		OF_ASSOC_ARRAY 	= 2,		//	Associative array
		OF_XML		 	= 3,		//	XML
		OF_CSV			= 4,		//	Comma-separated values
		OF_PSV			= 5			//	Pipe-separated values
	;
}

/**
 * A utility constants interface
 */
interface xlIUtility extends xlIConstantProvider
{
}

//*************************************************************************
//* Meaty Interfaces
//*************************************************************************

/**
 * This interface defines a configurable object
 */
interface xlIConfigurable extends xlIDebuggable
{
	/**
	 * Gets the configuration options
	 * @return array
	 */
	public function getOptions();

	/**
	 * Gets the configuration options
	 * @param array $options
	 * @return xlIConfigurable
	 */
	public function setOptions( $options = array() );

	/**
	 * Loads the configuration options
	 * @param array $options
	 * @return xlIConfigurable
	 */
	public function loadOptions( $options = array() );
}

/**
 * An interface for event classes
 */
interface xlIEvent extends xlIComponent, xlILog
{
}

/**
 * This interface indicates that a class is a behavior in YiiXL
 */
interface xlIBehavior extends IBehavior
{
	/**
	 * Bind a callback to an event
	 * @abstract
	 * @param xlIEvent|xlIComponent $caller
	 * @param string|null $eventName
	 * @param callback|null $callback
	 * @return boolean
	 */
	public function bind( $caller, $eventName = null, $callback = null );

	/**
	 * Unbind from an event
	 * @abstract
	 * @param string $eventName
	 * @return boolean
	 */
	public function unbind( $eventName );
}

/**
 * This interface defines constants for, and identifies an object as, a YiiXL component
 */
interface xlIApplicationComponent extends xlIConfigurable
{
}

//*************************************************************************
//*	Helpers
//*************************************************************************

/**
 * This interface defines a static helper class that can be mixed into the main
 * YiiXL system.
 */
interface xlIHelper extends xlIConfigurable
{
	/**
	 * Initializes the helper
	 */
	public static function initialize();
}

/**
 * This identifies a class as an object shifter.
 *
 * Object shifters are typical static function providers (helpers) that
 * shift the first argument off the parameter stack and use it as the target
 * object of the method called.
 *
 * Obviously, the sender must unshift itself onto the stack. The {@link YiiXL}
 * class provides methods to do this.
 *
 * @see YiiXL
 */
interface xlIShifter extends xlIHelper
{
}

/**
 * This interface defines a static UI helper class that can be mixed into the main
 * YiiXL system. It extends most constant providers
 */
interface xlIUIHelper extends xlIHelper, xlIForm
{
}

