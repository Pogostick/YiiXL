<?php
/**
 * This file is part of YiiXL
 * Copyright (c) 2009-2011, Pogostick, LLC. All rights reserved.
 *
 * @link http://www.pogostick.com Pogostick, LLC.
 * @license http://www.pogostick.com/licensing
 * @author Jerry Ablan <jablan@pogostick.com>
 *
 * @since v1.0.0
 *
 * @package yiixl
 * @subpackage core.components.config
 *
 * @filesource
 */
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
	const
		CLASS_LOG_TAG = 'yiixl.core.components.config.CXLConfigFile';

	/**
	 * The output formats available
	 */
	const
		ASSOC_ARRAY = 0,
		JSON = 1,
		XML = 2,
		INI = 3;
	
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

	/**
	 * @param string $path
	 * @return bool
	 */
	public function load( $path = null )
	{
		return false;
	}

	/**
	 * @param string $path
	 * @return bool
	 */
	public function save( $path = null )
	{
		return false;
	}

}