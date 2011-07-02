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
 * xlActiveRecordBehavior
 * The base class for all AR behaviors in the YiiXL system
 */
class xlActiveRecordBehavior extends xlComponentBehavior
{
	//********************************************************************************
	//* Constants
	//********************************************************************************

	/**
	 * Our logging tag
	 */
	const
		CLASS_LOG_TAG = 'yiixl.core.behaviors.xlActiveRecordBehavior';

	//********************************************************************************
	//* Public Methods
	//********************************************************************************

	/**
	 * @return bool|void
	 */
	public function init( )
	{
		//	Add our events
		$this->addEvents(
			array(
				'onBeforeSave' => 'beforeSave',
				'onAfterSave' => 'afterSave',
				'onBeforeDelete' => 'beforeDelete',
				'onAfterDelete' => 'afterDelete',
				'onBeforeFind' => 'beforeFind',
				'onAfterFind' => 'afterFind',
			)
		);

		return parent::init( );
	}

	//********************************************************************************
	//* Event Handlers
	//********************************************************************************

	/**
	 * Responds to {@link CActiveRecord::onBeforeSave} event.
	 * Overrides this method if you want to handle the corresponding event of the {@link CBehavior::owner owner}.
	 * You may set {@link xlEvent::isValid} to be false to quit the saving process.
	 * @param xlEvent $event event parameter
	 * @return bool
	 */
	public function beforeSave( $event )
	{
		return true;
	}

	/**
	 * Responds to {@link CActiveRecord::onAfterSave} event.
	 * Overrides this method if you want to handle the corresponding event of the {@link CBehavior::owner owner}.
	 * @param xlEvent $event event parameter
	 * @return bool
	 */
	public function afterSave( $event )
	{
		return true;
	}

	/**
	 * Responds to {@link CActiveRecord::onBeforeDelete} event.
	 * Overrides this method if you want to handle the corresponding event of the {@link CBehavior::owner owner}.
	 * You may set {@link xlEvent::isValid} to be false to quit the deletion process.
	 * @param CEvent $event event parameter
	 * @return bool
	 */
	public function beforeDelete( $event )
	{
		return true;
	}

	/**
	 * Responds to {@link CActiveRecord::onAfterDelete} event.
	 * Overrides this method if you want to handle the corresponding event of the {@link CBehavior::owner owner}.
	 * @param CEvent $event event parameter
	 * @return bool
	 */
	public function afterDelete( $event )
	{
		return true;
	}

	/**
	 * Responds to {@link CActiveRecord::onBeforeFind} event.
	 * Overrides this method if you want to handle the corresponding event of the {@link CBehavior::owner owner}.
	 * @param CEvent $event event parameter
	 * @return bool
	 * @since 1.0.9
	 */
	public function beforeFind( $event )
	{
		return true;
	}

	/**
	 * Responds to {@link CActiveRecord::onAfterFind} event.
	 * Overrides this method if you want to handle the corresponding event of the {@link CBehavior::owner owner}.
	 * @param CEvent $event event parameter
	 * @return bool
	 */
	public function afterFind( $event )
	{
		return true;
	}

}