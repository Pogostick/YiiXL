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
 * xlBehavior
 * The replacement class for Yii behaviors
 *
 * @property array $events
 * @property boolean $enabled
 * @property xlComponent $owner
 */
class xlBehavior extends xlComponent implements xlIBehavior
{
	//********************************************************************************
	//* Constants
	//********************************************************************************

	/**
	 * Our logging tag
	 */
	const
		CLASS_LOG_TAG = 'yiixl.core.behaviors.xlBehavior'
	;
	
	//********************************************************************************
	//* Properties
	//********************************************************************************

	/**
	 * @var array
	 */
	protected $_events = array();
	/**
	 * @var boolean
	 */
	protected $_enabled;
	/**
	 * @var xlComponent|xlApiComponent|xlApplicationComponent
	 */
	protected $_owner;

	//********************************************************************************
	//* Public Methods
	//********************************************************************************

	/**
	 * Declares events and the corresponding event handler methods.
	 * The events are defined by the {@link $owner} component, while the handler
	 * methods by the behavior class. The handlers will be attached to the corresponding
	 * events when the behavior is attached to the {@link $owner} component; and they
	 * will be detached from the events when the behavior is detached from the component.
	 * @param array $newEvents
	 * @return array The events (key) and handlers (value) for this object in an array
	 */
	public function addEvents( $newEvents = array() )
	{
		//	Merge any passed in events with mine and return the new array
		return array_merge(
			$newEvents,
			$this->getEvents()
		);
	}

	/**
	 * Attaches this behavior to the owner.
	 * The default implementation will set the {@link $owner} property
	 * and attach event handlers as declared in {@link events()}.
	 * Ensure the parent implementation is called when overridden.
	 * @param CComponent $owner the component that this behavior is to be attached to.
	 * @return \xlBehavior
	 */
	public function attach( $owner )
	{
		$this->_owner = $owner;

		foreach ( $this->events() as $_event => $_handler )
		{
			$owner->attachEventHandler( $_event, array( $this, $_handler ) );
		}

		return $this;
	}

	/**
	 * Detaches the behavior object from the component.
	 * The default implementation will unset the {@link $owner} property
	 * and detach event handlers declared in {@link events()}.
	 * Make sure you call the parent implementation if you override this method.
	 * @param CComponent $owner the component from which to detach this behavior
	 * @return \xlBehavior
	 */
	public function detach( $owner )
	{
		foreach ( $this->events() as $_event => $_handler )
		{
			$owner->detachEventHandler( $_event, array( $this, $_handler ) );
		}

		$this->_owner = null;
		
		return $this;
	}

	//********************************************************************************
	//* Property Accessors
	//********************************************************************************

	/**
	 * @param array $events
	 * @return \xlBehavior
	 */
	public function setEvents( $events )
	{
		$this->_events = $events;
		return $this;
	}

	/**
	 * @return array
	 */
	public function getEvents()
	{
		return $this->_events;
	}

	/**
	 * Alias of getEvents() to overcome broken Yii object model
	 * @return array The events (key) and handlers (value) for this object in an array
	 */
	public function events()
	{
		return $this->getEvents();
	}

	/**
	 * @param boolean $value whether this behavior is enabled
	 * @return \xlBehavior
	 */
	public function setEnabled( $value = true )
	{
		if ( null !== $this->_owner && $value !== $this->_enabled )
		{
			foreach ( $this->events() as $_event => $_handler )
			{
				$this->_owner->setEventHandler( $_event, array( $this, $_handler ), $value );
			}
		}

		$this->_enabled = $value;
		
		return $this;
	}

	/**
	 * @return boolean
	 */
	public function getEnabled()
	{
		return $this->_enabled;
	}

	/**
	 * @param \xlComponent $owner
	 * @return \xlBehavior $this
	 */
	public function setOwner( $owner )
	{
		$this->_owner = $owner;
		return $this;
	}

	/**
	 * @return \xlComponent
	 */
	public function getOwner()
	{
		return $this->_owner;
	}
	
}
