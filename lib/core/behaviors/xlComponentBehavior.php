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
 * xlComponentBehavior
 * The base class for all AR behaviors in the YiiXL system
 */
class xlComponentBehavior extends xlBehavior
{
	//********************************************************************************
	//* Constants
	//********************************************************************************

	/**
	 * @const string Our logging tag
	 */
	const	
		CLASS_LOG_TAG = 'yiixl.core.behaviors.xlComponentBehavior';

	//********************************************************************************
	//* Public Methods
	//********************************************************************************

	/**
	 * @return bool|void
	 */
	public function init()
	{
		//	Add our events
		$this->setEvents(
			array(
				'onAfterConstruct' => 'afterConstruct',
				'onBeforeValidate' => 'beforeValidate',
				'onAfterValidate' => 'afterValidate',
			)
		);

		return parent::init();
	}

	//********************************************************************************
	//* Event Handlers
	//********************************************************************************

	/**
	 * @param CEvent $event
	 * @return bool
	 */
	public function afterConstruct( $event )
	{
		return true;
	}

	/**
	 * @param CEvent $event
	 * @return bool
	 */
	public function beforeValidate( $event )
	{
		return true;
	}

	/**
	 * @param CEvent $event
	 * @return bool
	 */
	public function afterValidate( $event )
	{
		return true;
	}
	
	//********************************************************************************
	//* Property
	//********************************************************************************

}