<?php
/**
 * This file is part of the YiiXL package.
 *
 * @copyright Copyright (c) 2009-2011 Pogostick, LLC.
 * @link http://www.pogostick.com Pogostick, LLC.
 * @license http://www.pogostick.com/licensing
 * @filesource
 *
 * @package		yiixl
 * @subpackage 	core.interfaces
 * @author			Jerry Ablan <jablan@pogostick.com>
 */

/**
 * This interface does nothing other than indicate that an exception is
 * part of the YiiXL package.
 *
 * @package 	yiixl
 * @subpackage 	base
 *
 * @author 		Jerry Ablan <jablan@pogostick.com>
 * @version 	SVN $Id: IXL
 * @since 		v1.0.6
 *
 * @filesource
 */
interface IXLException
{
}

/**
 * This interface defines constants for, and identifies an object as, a YiiXL component
 *
 * @package		yiixl
 * @subpackage 	core.interfaces
 *
 * @author			Jerry Ablan <jablan@pogostick.com>
 * @version		SVN $Id: IXLComponent.php 358 2010-01-02 23:33:40Z jerryablan@gmail.com $
 * @since			v1.0.6
 */
interface IXLComponent
{
	/**
	 * Gets the debug level
	 * @return integer
	 */
	public function getDebugLevel();

	/**
	 * Sets the debug level
	 * @param integer The new debug level
	 */
	public function setDebugLevel( $value = false );

	/**
	 * Gets the configuration options
	 * @return array
	 */
	public function getOptions();

	/**
	 * Loads the configuration options
	 * @param array $options
	 * @return CXLComponent
	 */
	public function loadOptions( $options = array() );

	/**
	 * Gets behavior methods. Needed to implement lightweight helper classes
	 * @return array
	 */
	public function getBehaviorMethods();

}

/**
 * This interface indicates that a class is a behavior in YiiXL
 */
interface IXLBehavior extends IXLComponent
{
}

/**
 * This interface defines a logger
 */
interface IXLLogger
{
	//********************************************************************************
	//* Constants for all helpers
	//********************************************************************************

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
 * This interface defines a static helper class that can be mixed into the main
 * YiiXL system.
 */
interface IXLHelper
{
	//********************************************************************************
	//* Public Methods
	//********************************************************************************

	public static function initialize();

}

/**
 * This interface defines a static UI helper class that can be mixed into the main
 * YiiXL system.
 */
interface IXLUIHelper extends IXLHelper
{
	//********************************************************************************
	//* Constants for UI helpers
	//********************************************************************************

	/**
	* Standard output formats
	*/
	const
		OF_JSON 		= 0,
		OF_HTTP 		= 1,
		OF_ASSOC_ARRAY 	= 2,
		OF_XML		 	= 3;

	/**
	* Pager locations
	*/
	const
		PL_TOP_LEFT		= 0,
		PL_TOP_RIGHT	= 1,
		PL_BOTTOM_LEFT	= 2,
		PL_BOTTOM_RIGHT	= 3;

	/***
	* Predefined action types for CPSForm
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
		ACTION_UNLOCK 	= 8;

	//	Add your own in between 4 and 997...
	const
		ACTION_PREVIEW 		= 996,
		ACTION_RETURN 		= 997,
		ACTION_CANCEL 		= 998,
		ACTION_GENERIC 		= 999;

}

interface IXLController
{
	//********************************************************************************
	//* Constants
	//********************************************************************************

	/**
	* The number of items to display per page
	*/
	const
		PAGE_SIZE = 10;

	/**
	* Indexes into {@link CXLController#m_arUserActionMap}
	*/
	const
		ACCESS_TO_ALL = 0,
		ACCESS_TO_ANY = 0,
		ACCESS_TO_ANON = 0,
		ACCESS_TO_GUEST = 1,
		ACCESS_TO_AUTH = 2,
		ACCESS_TO_ADMIN = 3,
		ACCESS_TO_SUPERADMIN = 5;

	//	Last...
	const
		ACCESS_TO_NONE = 6;

	/**
	 * The name of our command form field
	 */
	const
		COMMAND_FIELD_NAME = '__yxl';

	/**
	 * Standard search text for rendering
	 */
	const
		SEARCH_HELP_TEXT = 'You may optionally enter a comparison operator (<b>&lt;</b>, <b>&lt;=</b>, <b>&gt;</b>, <b>&gt;=</b>, <b>&lt;&gt;</b>or <b>=</b>) at the beginning of each search value to specify how the comparison should be done.';

}

/**
 * This interface defines an object as an access control object
 */
interface IXLAccessControl
{
	/**
	* Indices into {@link CXLController::actionControlMap}
	*/
	const 
		ACCESS_TO_NONE = -1,
		ACCESS_TO_ANY = 0,
		ACCESS_TO_GUEST = 1,
		ACCESS_TO_AUTH = 2,
		ACCESS_TO_ADMIN = 3,
		ACCESS_TO_ADMIN_LEVEL_0 = 3,
		ACCESS_TO_ADMIN_LEVEL_1 = 4,
		ACCESS_TO_ADMIN_LEVEL_2 = 5;
}

/**
 * This identifies a class as an object shifter.
 * Object shifters are typical static function providers (helpers) that
 * shift the first argument off the parameter stack and use it  as the target 
 * object of the method called.
 * 
 * Obviously, the sender must unshift itself onto the stack. The {@link YiiXL} 
 * class provides methods to do this.
 */
interface IXLShifter extends IXLHelper
{
}

/**
 * This identifies a helper class as a utility provider
 */
interface IXLUtilityHelper extends IXLAccessControl
{
}