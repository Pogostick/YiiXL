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
 * @package			yiixl.core.behaviors
 * @category		Behaviors
 * @since			v1.0.0
 *
 * @filesource
 */
/**
 * xlActionControlBehavior
 * Provides an alternative interface to configuring access to your actions
 *
 * @property-read array $userActionMap
 */
class xlActionControlBehavior extends xlBehavior implements xlIAccess
{
	//********************************************************************************
	//* Member Variables
	//********************************************************************************

	/**
	 * @var array Action access filters
	 */
	protected $_userActionMap = array();

	//********************************************************************************
	//* Public Methods
	//********************************************************************************

	/**
	 * Initialize
	 * @return bool|void
	 */
	public function init()
	{
		parent::init();

	}

	/**
	 * Resets the UAM
	 * @return \xlActionControlBehavior
	 */
	protected function resetUserActionMap()
	{
		$this->_userActionMap = array();
		$this->addUserAction( self::ACCESS_TO_ANY, 'error' );
		return $this;
	}

	/**
	 * Adds a role to an access level/action
	 * @param integer $accessLevel
	 * @param string $action
	 * @param string $role
	 * @return \xlActionControlBehavior
	 */
	public function addUserActionRole( $accessLevel, $action, $role )
	{
		array_push( $this->_userActionMap[$accessLevel]['roles'], $value );
		return $this;
	}

	/**
	 * Removes an access level/action pair
	 * @param integer $accessLevel
	 * @param string $action
	 * @return mixed Returns the previous value or null
	 */
	public function removeUserAction( $accessLevel, $action )
	{
		return XL::o( $this->_userActionMap, $accessLevel, null, true );
	}

	/**
	 * Adds a single action access filter
	 * @param int $accessLevel
	 * @param string $action
	 * @return \xlActionControlBehavior
	 */
	public function addUserAction( $accessLevel, $action )
	{
		$_map = XL::o( $this->_userActionMap, $accessLevel, array() );

		if ( !in_array( $action, $_map ) )
		{
			array_push( $_map, $action );
		}

		//	Make sure we don't lose our error handler...
		if ( self::ACCESS_TO_ANY == $accessLevel )
		{
			if ( !in_array( 'error', $_map ) )
			{
				array_push( $_map, 'error' );
			}
		}

		return $this;
	}

	/**
	 * @param integer $accessLevel
	 * @param array $actions
	 * @return \xlActionControlBehavior
	 */
	public function addUserActions( $accessLevel, $actions = array() )
	{
		foreach ( $actions as $_action )
		{
			$this->addUserAction( $accessLevel, $_action );
		}

		return $this;
	}

	/**
	 * Gets an UAM
	 * @param string $accessLevel Retrieves the UAM for this access level.
	 * @return array|null
	 */
	public function getUserActionMap( $accessLevel )
	{
		return XL::o( $this->_userActionMap, $accessLevel );
	}

	/**
	 * Resets the UAM
	 * @param integer $accessLevel
	 * @param array $value
	 * @return xlActionControlBehavior
	 */
	protected function _setUserActionMap( $accessLevel, $value = array() )
	{
		$this->_userActionMap[$accessLevel] = $value;
		return $this;
	}

}