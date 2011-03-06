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
	 * Gets configuration options
	 * @return array
	 */
	public function getOptions();

}

/**
 * This interface indicates that a class is a behavior in YiiXL
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
interface IXLBehavior extends IXLComponent
{
}

/**
 * This interface defines a YiiXL Helper class
 */
interface IXLHelper
{
	//********************************************************************************
	//* Constants for all helpers
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
