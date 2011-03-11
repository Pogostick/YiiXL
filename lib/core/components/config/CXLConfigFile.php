<?php
/**
 * This file is part of the YiiXL package.
 *
 * @copyright Copyright (c) 2009-2011 Pogostick, LLC.
 * @link http://www.pogostick.com Pogostick, LLC.
 * @license http://www.pogostick.com/licensing
 *
 * @package yiixl
 * @subpackage core.components
 *
 * @author 		Jerry Ablan <jablan@pogostick.com>
 * @since 		v1.0.0
 *
 * @filesource
 */

//	Include Files
//	Constants
//	Global Settings

/**
 * CXLConfigFile implements the CXLConfig class that stored in a file
 */
class CXLConfigFile extends CXLConfig implements Countable, Iterator
{
	//********************************************************************************
	//* Constants
	//********************************************************************************
	
	/**
	 * Our logging tag
	 */
	const	CLASS_LOG_TAG = 'yiixl.core.components.config.CXLConfigFile';

	/**
	 * The output formats available
	 */
	const	ASSOC_ARRAY = 0;
	const	JSON = 1;
	const	XML = 2;
	const	INI = 3;
	
	//********************************************************************************
	//* Member Variables
	//********************************************************************************

	/**
	 * The configuration storage format object to use for writing the configuration.
	 * @var string
	 */
	protected $_configFileFormat = self::ASSOC_ARRAY;

	/**
	 * The configuration file name
	 * @var string
	 */
	protected $_configFileName = 'config.php';

	/**
	 * The configuration file path
	 * @var string
	 */
	protected $_configFilePath = 'application.config';

	//********************************************************************************
	//* Public Methods
	//********************************************************************************

	public function load( $path = null )
	{
	}
	
	public function save( $path = null )
	{
	}

}