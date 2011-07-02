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
 * @package			yiixl.core.components
 * @category		Components
 * @since			v1.0.0
 *
 * @brief 			This file contains all the YiiXL interfaces
 *
 * @filesource
 */
/**
 * xlApplicationComponent class
 * This is the base class for application-level YiiXL objects. It extends the base
 * functionality of the Yii Framework by replacing the CApplicationComponent object
 * @property array $behaviors
 * @property boolean $initialized
 */
abstract class xlApplicationComponent extends xlComponent implements xlIApplicationComponent
{
	//********************************************************************************
	//* Constants
	//********************************************************************************

	/**
	 * Our logging tag
	 */
	const
		CLASS_LOG_TAG = 'yiixl.core.components.xlApplicationComponent'
	;

	//********************************************************************************
	//* Properties
	//********************************************************************************

	/**
	 * @var array the behaviors that should be attached to this component.
	 */
	protected $_behaviors = array();
	/**
	 * @var bool
	 */
	protected $_initialized = false;

	//********************************************************************************
	//* Public Methods
	//********************************************************************************

	/**
	 * Constructs a component.
	 * All components accept an array of configuration options. These options are placed into accessible
	 * members (via "setter"). The entire array is stored in the member {@see xlIComponent::options}.
	 * @return \xlApplicationComponent
	 */
	public function init()
	{
		parent::init();

		//	Initialize...
		$this->attachBehaviors( $this->_behaviors );
		$this->_initialized = true;
	}

	//*************************************************************************
	//* Properties
	//*************************************************************************

	/**
	 * @param array $behaviors
	 * @return \xlApplicationComponent $this
	 */
	public function setBehaviors( $behaviors )
	{
		$this->_behaviors = $behaviors;
		return $this;
	}

	/**
	 * @return array
	 */
	public function getBehaviors()
	{
		return $this->_behaviors;
	}

	/**
	 * getBehaviors alias to inconsistent Yii object model
	 * @return array
	 * @deprecated Please use the real getter.
	 */
	public function behaviors()
	{
		return $this->_behaviors;
	}

	/**
	 * @param boolean $initialized
	 * @return \xlApplicationComponent $this
	 */
	public function setInitialized( $initialized )
	{
		$this->_initialized = $initialized;
		return $this;
	}

	/**
	 * @return boolean
	 */
	public function getInitialized()
	{
		return $this->_initialized;
	}

}