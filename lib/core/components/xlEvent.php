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
 * @category		Behaviors
 * @since			v1.0.0
 *
 * @filesource
 */
/**
 * xlEvent
 * Encapsulates the parameters associated with an event
 *
 * @property xlComponent $creator
 * @property array|object $eventData
 * @property bool $handled
 */
class xlEvent extends xlComponent
{
	//********************************************************************************
	//* Member Variables
	//********************************************************************************

	/**
	 * @var xlComponent the origin of this event
	 */
	protected $_creator;
	/**
	 * @var array|object Any event information the creator wants to convey
	 */
	protected $_eventData;
	/**
	 * @var boolean Indicates if this event has been handled
	 */
	protected $_handled;

	//********************************************************************************
	//* Constructor
	//********************************************************************************

	/**
	 * Constructor
	 * @param xlComponent $creator
	 * @param array $options
	 * @return \xlEvent
	 */
	public function __construct( $creator = null, $options = array() )
	{
		$this->_creator = $creator;
		parent::__construct( $options );
	}

	//*************************************************************************
	//* Properties
	//*************************************************************************

	/**
	 * @param \xlComponent $creator
	 * @return \xlEvent $this
	 */
	public function setCreator( $creator )
	{
		$this->_creator = $creator;
		return $this;
	}

	/**
	 * @return \xlComponent
	 */
	public function getCreator()
	{
		return $this->_creator;
	}

	/**
	 * @param array|object $eventData
	 * @return \xlEvent $this
	 */
	public function setEventData( $eventData )
	{
		$this->_eventData = $eventData;
		return $this;
	}

	/**
	 * @return array|object
	 */
	public function getEventData()
	{
		return $this->_eventData;
	}

	/**
	 * @param boolean $handled
	 * @return \xlEvent $this
	 */
	public function setHandled( $handled )
	{
		$this->_handled = $handled;
		return $this;
	}

	/**
	 * @return boolean
	 */
	public function getHandled()
	{
		return $this->_handled;
	}

}