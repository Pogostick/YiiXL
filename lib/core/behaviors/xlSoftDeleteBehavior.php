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
 * @brief
 *
 * @filesource
 */
/**
 * xlSoftDeleteBehavior
 * Provides soft-deleting of records
 *
 * @property string $softDeleteColumnName The attribute which indicates a soft-delete
 * @property array $softDeleteValues Two item array containing the [false,true] values for soft-deletion. Defaults to array(0,1) ('false' and 'true' respectively).
 *
 */
class xlSoftDeleteBehavior extends xlActiveRecordBehavior
{
	//********************************************************************************
	//* Member Variables
	//********************************************************************************

	/**
	 * @var string If not null, all deletes are soft
	 */
	protected $_softDeleteColumnName = null;
	/**
	 * @var array Soft delete indicator [ 0 => <value for false>, 1 => <value for true> ].
	 */
	protected $_softDeleteValues = array( 
		0, 
		1
	);

	//********************************************************************************
	//* Public Methods
	//********************************************************************************

	/**
	 * Undeletes a soft-deleted model
	 * @return boolean
	 */
	public function undelete()
	{
		if ( null !== $this->_softDeleteColumnName )
		{
			//	Perform a soft delete if this model allows
			if ( $this->_owner->hasAttribute( $this->_softDeleteColumnName ) )
			{
				$this->setAttribute( $this->_softDeleteColumnName, XL::o( $this->_softDeleteValues, 0, 0 ) );
				return $this->update( array( $this->_softDeleteColumnName ) );
			}
		}

		//	Otherwise, not possible
		return false;
	}

	//********************************************************************************
	//*  Event Handlers
	//********************************************************************************

	/**
	 * Soft deletes models that have that feature
	 * @params CEvent $event
	 * @return boolean
	 * @throws CDbException Thrown when we can't update the value
	 */
	public function beforeDelete( $event )
	{
		//	Pass it on...
		if ( parent::beforeDelete( $event ) )
		{
			//	We want to be the top of the chain...
			if ( $this->_softDeleteColumnName && $event->isValid && ! $event->handled )
			{
				//	Perform a soft delete if this model allows
				if ( $event->getSender()->hasAttribute( $this->_softDeleteColumnName ) )
				{
					$event->isValid = false;
					$event->handled = true;
					$event->sender->setAttribute( $this->_softDeleteColumnName, XL::o( $this->_softDeleteValues, 1, 1 ) );

					if ( !$event->sender->update( array( $this->_softDeleteColumnName ) ) ) 
						throw new xlDatabaseException( 'Error saving soft delete row.' );
				}
			}

			return true;
		}

		return false;
	}

	/**
	 * Automatically exclude soft-deleted rows
	 * @param CEvent $event
	 */
	public function beforeFind( $event )
	{
		if ( $this->_softDeleteColumnName && $this->getOwner()->hasAttribute( $this->_softDeleteColumnName ) )
		{
			//	Merge in the soft delete indicator
			$event->getSender()->getDbCriteria()->mergeWith(
				array(
					'condition' => $this->_softDeleteColumnName . ' = :softDeleteValue',
					'params' => array( ':softDeleteValue' => XL::o( $this->_softDeleteValues, 0, 0 ) ),
				)
			);
		}

		//	Pass it on...
		return parent::beforeFind( $event );
	}

	//********************************************************************************
	//* Properties
	//********************************************************************************

	/**
	 * Returns the soft-delete column for this model
	 * @return string
	 */
	public function getSoftDeleteColumnName()
	{
		return $this->_softDeleteColumnName;
	}

	/**
	 * Sets the soft-delete column for this model
	 * @param $value
	 */
	public function setSoftDeleteColumnName( $value )
	{
		$this->_softDeleteColumnName = $value;
	}

	/**
	 * Returns the soft-delete values for this model [false,true]
	 * @return array
	 */
	public function getSoftDeleteValues()
	{
		return $this->_softDeleteValues;
	}

	/**
	 * Sets the soft-delete values for this model
	 * @var array $array The true/false values for soft-deletion.
	 */
	public function setSoftDeleteValues( $array )
	{
		$this->_softDeleteValues = $array;
	}

}