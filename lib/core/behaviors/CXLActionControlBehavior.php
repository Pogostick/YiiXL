<?php

/**
 * This file is part of the YiiXL package.
 *
 * @copyright		Copyright (c) 2009-2011 Pogostick, LLC.
 * @link			http://www.pogostick.com Pogostick, LLC.
 * @license		http://www.pogostick.com/licensing
 * @package		yiixl
 * @subpackage		core.behaviors
 * @author			Jerry Ablan <jablan@pogostick.com>
 * @filesource
 */

/**
 * CXLUserActionBehavior
 * Provides an alternative interface to configuring access to your actions
 */
class CXLUserActionBehavior extends CBehavior implements IXLBehavior, IXLAccessControl
{
	//********************************************************************************
	//* Member Variables
	//********************************************************************************

	/**
	 * @var array $_userActionMap An array of actions permitted by any user
	 * @access protected
	 */
	protected $_userActionMap = array();

	//********************************************************************************
	//* Public Methods
	//********************************************************************************

	/**
	 * Gets an UAM
	 * @param integer $accessLevel
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
	 * @param integer $accessLevel
	 * @param array $value
	 * @return CXLUserActionBehavior 
	 */
	protected function setUserActionMap( $accessLevel, $value = array() )
	{
		$this->_userActionMap[$accessLevel] = $value;
		return $this->getOwner();
	}

	/**
	 * Adds a role to an access level/action
	 * @param integer $accessLevel
	 * @param string $action
	 * @param string $role
	 */
	public function addUserActionRole( $accessLevel, $action, $role )
	{
		array_push( $this->_userActionMap[$accessLevel]['roles'], $value );
		return $this->getOwner();
	}

	/**
	 * Removes an access level/action pair
	 * @param integer $accessLevel
	 * @param string $action
	 * @return mixed Returns the previous value or null
	 */
	public function removeUserAction( $accessLevel, $action )
	{
		return PS::o( $this->_userActionMap, $accessLevel, null, true );
	}

	/**
	 * @param type $accessLevel
	 * @param type $action
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
	 * @param integer $accessLevel
	 * @param array $actions
	 */
	public function addUserActions( $accessLevel, $actions = array() )
	{
		foreach ( $actions as $_action ) 
			$this->addUserAction( $accessLevel, $_action );
		
		return $this->getOwner();
	}

}