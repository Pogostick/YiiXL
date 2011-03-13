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
 * @since 		v1.1.0
 *
 * @filesource
 */

/**
 * The mother of all helper classes
 */
class CXLBaseHelper extends CXLComponent implements IXLHelper
{
	//********************************************************************************
	//* Constants
	//********************************************************************************

	/**
	 * Our logging tag
	 */
	const	CLASS_LOG_TAG = 'yiixl.core.helpers.CXLBaseHelper';

	//********************************************************************************
	//* Public Methods
	//********************************************************************************
	
	/**
	 * Initialize ourselves
	 */
	public static function initialize()
	{
	}
}