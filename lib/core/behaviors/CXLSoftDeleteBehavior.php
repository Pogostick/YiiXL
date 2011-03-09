<?php
/**
 * This file is part of the psYiiExtensions package.
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
 * CXLSoftDeleteBehavior
 * Provides soft-deleting of records
 *
 * @property string $softDeleteColumnName The attribute which indicates a soft-delete
 * @property array $softDeleteValue Two item array containing the [false,true] values for soft-deletion. Defaults to array(0,1) ('false' and 'true' respectively).
 *
 */
class CXLSoftDeleteBehavior extends CXLActiveRecordBehavior
{
	//********************************************************************************
	//* Member Variables
	//********************************************************************************

	/**
	* @var string $_softDeleteColumnName If defined, all deletes are soft
	*/
	protected $_softDeleteColumnName = null;

	/**
	* @var array $_softDeleteValues Soft delete indicator [false,true]
	*/
	protected $_softDeleteValues = array( 0, 1 );

	//********************************************************************************
	//* Properties
	//********************************************************************************

	/**
	* Returns the soft-delete column for this model
	* @return string
	*/
	public function getSoftDeleteColumnName() { return $this->_softDeleteColumnName; }

	/**
	* Sets the soft-delete column for this model
	* @var string
	*/
	public function setSoftDeleteColumnName( $sValue ) { $this->_softDeleteColumnName = $sValue; }

	/**
	* Returns the soft-delete values for this model [false,true]
	* @return array
	*/
	public function getSoftDeleteValues() { return $this->_softDeleteValues; }

	/**
	* Sets the soft-delete values for this model
	* @var array $arValue The true/false values for soft-deletion.
	*/
	public function setSoftDeleteValues( $arValue ) { $this->_softDeleteValues = $arValue; }

	//********************************************************************************
	//* Public Methods
	//********************************************************************************

	/**
	* Undeletes a soft-deleted model
	* @return boolean
	*/
	public function undelete()
	{
		if ( $this->_softDeleteColumnName )
		{
			//	Perform a soft delete if this model allows
			if ( $this->hasAttribute( $this->_softDeleteColumnName ) )
			{
				$this->setAttribute( $this->_softDeleteColumnName, $this->_softDeleteValues[ 0 ] );
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
				if ( $event->sender->hasAttribute( $this->_softDeleteColumnName ) )
				{
					$event->isValid = false;
					$event->handled = true;
					$event->sender->setAttribute( $this->_softDeleteColumnName, $this->_softDeleteValues[ 1 ] );

					if ( ! $event->sender->update( array( $this->_softDeleteColumnName ) ) )
						throw new CDbException( 'Error saving soft delete row.' );
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
		if ( $this->_softDeleteColumnName && $this->owner->hasAttribute( $this->_softDeleteColumnName ) )
		{
			//	Merge in the soft delete indicator
			$event->sender->getDbCriteria()->mergeWith(
				array(
					'condition' => $this->_softDeleteColumnName . ' = :softDeleteValue',
					'params' => array( ':softDeleteValue' => $this->_softDeleteValues[ 0 ] ),
				)
			);
		}

		//	Pass it on...
    	return parent::beforeFind( $event );
    }

}