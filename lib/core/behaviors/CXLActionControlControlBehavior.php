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
 * CXLActionControlBehavior
 * Provides an alternative interface to configuring access to your actions
 */
class CXLActionControlBehavior extends CXLBehavior implements IXLAccessControl
{
	//********************************************************************************
	//* Member Variables
	//********************************************************************************

	/**
	 * An array of actions permitted by any user
	 */
	protected $_userActionMap = array();

	//********************************************************************************
	//* Public Methods
	//********************************************************************************

	/**
	 * Gets an UAM
	 * @param \$accessLevel Retrieves the UAM for this access level.
	 * @return array
	 */
	public function getUserActionMap( $accessLevel )
	{
		return PS::o( $this->_userActionMap, $accessLevel );
	}

	/**
	 * Resets the UAM
	 */
	protected function resetUserActionMap()
	{
		$this->_userActionMap = array();
		$this->addUserAction( self::ACCESS_TO_ANY, 'error' );
		return $this->getOwner();
	}

	/**
	 * Resets the UAM
	 * @param integer \$accessLevel
	 * @param array \$value
	 * @return CXLActionControlBehavior
	 */
	protected function setUserActionMap( $accessLevel, $value = array() )
	{
		$this->_userActionMap[$accessLevel] = $value;
		return $this->getOwner();
	}

	/**
	 * Adds a role to an access level/action
	 * @param integer \$accessLevel
	 * @param string \$action
	 * @param string \$role
	 */
	public function addUserActionRole( $accessLevel, $action, $role )
	{
		array_push( $this->_userActionMap[$accessLevel]['roles'], $value );
		return $this->getOwner();
	}

	/**
	 * Removes an access level/action pair
	 * @param integer \$accessLevel
	 * @param string \$action
	 * @return mixed Returns the previous value or null
	 */
	public function removeUserAction( $accessLevel, $action )
	{
		return PS::o( $this->_userActionMap, $accessLevel, null, true );
	}

	/**
	 * @param type \$accessLevel
	 * @param type \$action
	 * @return mixed
	 */
	public function addUserAction( $accessLevel, $action )
	{
		$_map = PS::o( $this->_userActionMap, $accessLevel, array() );

		if ( ! in_array( $action, $_map ) )
			array_push( $_map, $action );

		//	Make sure we don't lose our error handler...
		if ( self::ACCESS_TO_ANY == $accessLevel )
		{
			if ( ! in_array( 'error', $_map ) )
				array_push( $_map, 'error' );
		}
		
		return $this->setUserActionMap( $accessLevel, $_map );
	}

	/**
	 * @param integer \$accessLevel
	 * @param array \$actions
	 */
	public function addUserActions( $accessLevel, $actions = array() )
	{
		foreach ( $actions as $_action ) 
			$this->addUserAction( $accessLevel, $_action );
		
		return $this->getOwner();
	}

}