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
 * @subpackage core.behaviors
 *
 * @filesource
 */

/**
 * CXLBehavior
 * The base class for all non-AR behaviors in the YiiXL system
 */
class CXLBehavior extends CBehavior implements IXLBehavior
{
	//********************************************************************************
	//* Constants
	//********************************************************************************

	/**
	 * Our logging tag
	 */
	const	CLASS_LOG_TAG = 'yiixl.core.behaviors.CXLBehavior';

	//********************************************************************************
	//* Properties
	//********************************************************************************

	/**
	 * User-defined debug flag. false = OFF, anything else is up to you.
	 * @var integer $_debugLevel
	 */
	protected $_debugLevel = false;

	/**
	 * Our configuration options
	 * @var array $_options
	 */
	protected $_options;

	/**
	 * Imported attached behavior methods
	 * @var array $_behaviorMethods
	 */
	protected $_behaviorMethods;

	//********************************************************************************
	//* Property Accessors
	//********************************************************************************

	/**
	 * Gets the debug level
	 * @return integer
	 */
	public function getDebugLevel()
	{
		return $this->_debugLevel;
	}

	/**
	 * Sets the debug level
	 * @param integer The new debug level
	 * @return $this
	 */
	public function setDebugLevel( $value = false )
	{
		$this->_debugLevel = $value;
		return $this;
	}

	/**
	 * Gets the configuration options
	 * @return array
	 */
	public function getOptions()
	{
		return $this->_options;
	}

	/**
	 * Gets the list of registered behavior methods
	 * @return array
	 */
	public function getBehaviorMethods()
	{
		return $this->_behaviorMethods;
	}

	/**
	 * Loads the configuration options
	 * @param array
	 * @return $this
	 */
	public function loadOptions( $options = array() )
	{
		$this->_options = $options;
		return $this;
	}

}