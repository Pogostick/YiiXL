<?php
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
 * @subpackage core.components
 *
 * @filesource
 */
/**
 * xlWebModule provides extra functionality to the base module functionality of Yii
 */
class xlWebModule extends CWebModule implements xlIComponent
{
	//********************************************************************************
	//* Constants
	//********************************************************************************

	/**
	 * Our logging tag
	 */
	const	CLASS_LOG_TAG = 'yiixl.core.components.xlWebModule';

	//********************************************************************************
	//* Private Members & Accessors
	//********************************************************************************
	
	protected $_configPath = null;
	public function getConfigPath() { return $this->_configPath; }
	public function setConfigPath( $sValue ) { $this->_configPath = $sValue; }

	protected $_assetPath = null;
	public function getAssetPath() { return $this->_assetPath; }
	public function setAssetPath( $sValue ) { $this->_assetPath = $sValue; }
	
	protected $_assetUrl = null;
	public function getAssetUrl() { return $this->_assetUrl; }
	protected function setAssetUrl( $sUrl ) { $this->_assetUrl = $sUrl; }

	//********************************************************************************
	//* Public Methods
	//********************************************************************************
	
	/**
	* Initialize
	* 
	*/
	public function init()
	{
		//	Phone home...
		parent::init();
		
		//	import the module-level models and components
		$this->setImport(
			array(
				$this->id . '.models.*',
				$this->id . '.components.*',
			)
		);

		//	Read private configuration...
		if ( ! empty( $this->_configPath ) ) $this->configure( require( $this->basePath . $this->_configPath ) );
		
		//	Get our asset manager going...
		$this->_setAssetPaths();
	}

	//********************************************************************************
	//* Private Methods
	//********************************************************************************

	/**
	 * Initializes the asset manager for this module
	 */
	protected function _setAssetPaths()
	{
		$_assetManager = XL::_a()->getAssetManager();
		if ( ! $this->_assetPath ) 
			$this->_assetPath = $_assetManager->getBasePath() . DIRECTORY_SEPARATOR . $this->getId();
		
		if ( ! is_dir( $this->_assetPath ) ) 
			@mkdir( $this->_assetPath );
		
		$this->_assetUrl = $_assetManager->publish( $this->_assetPath, true, -1 );
	}
	
}