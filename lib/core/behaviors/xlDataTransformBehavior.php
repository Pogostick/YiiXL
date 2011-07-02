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
 * xlDataTransformBehavior
 *
 * If attached to a model, fields are transformed per your configuration.
 * Also provides a default sort for a model
 * 
 * @property string $format
 * @property string $defaultSort
 */
class xlDataTransformBehavior extends xlActiveRecordBehavior
{
	//********************************************************************************
	//* Member Variables
	//********************************************************************************

	/**!
	 * Holds the default/configured formats for use when populating fields
	 *
	 * array(
	 * 	'eventName' => array(      		//	The event to apply format in
	 * 		'dataType' => <format>		//	The format for the display
	 * 		'method' => <function>		//	The function to call for formatting
	 * 	),								//		Send array(object,method) for class methods
	 * 	'eventName' => array(      		//	The event to apply format in
	 * 		'dataType' => <format>		//	The format for the display
	 * 		'method' => <function>		//	The function to call for formatting
	 * 	),								//		Send array(object,method) for class methods
	 * 	...
	 *
	 * @var array $_dateFormat
	 */
	protected $_dateFormat = array(

		//	After a find, the date is reformatted as so
		'afterFind' => array(
			'date' => 'm/d/Y',
			'datetime' => 'm/d/Y H:i:s',
		),
		//	After a validate, the date is reformatted as so
		'afterValidate' => array(
			'date' => 'Y-m-d',
			'datetime' => 'Y-m-d H:i:s',
		),

	);

	/**
	 * Default sort
	 * @var string $_defaultSort
	 * @see getDefaultSort
	 * @see setDefaultSort
	 */
	protected $_defaultSort;

	//********************************************************************************
	//* Protected Methods
	//********************************************************************************

	/**
	 * Applies the requested format to the value and returns it.
	 * Override this method to apply additional format types.
	 *
	 * @param CDbColumnSchema $column
	 * @param mixed $value
	 * @param string $eventName
	 * @return mixed
	 */
	protected function _applyFormat( $column, $value, $eventName = 'view' )
	{
		$_result = $value;

		//	Apply formats
		switch ( $column->dbType )
		{
			case 'date':
			case 'datetime':
			case 'timestamp':
				//	Handle blanks
				if ( null != $value && $value != '0000-00-00' && $value != '0000-00-00 00:00:00' && false !== strtotime( $value ) )
				{
					$_result = date( $this->getFormat( $eventName, $column->dbType ), strtotime( $value ) );
				}
				break;
		}

		return $_result;
	}

	/**
	 * Process the data and apply formats
	 * @param string $eventName
	 * @param CEvent $event
	 * @return mixed
	 */
	protected function _handleEvent( $eventName, CEvent $event )
	{
		static $_schema;
		static $_schemaFor;
		
		/** @var $_model CActiveRecord */
		$_model = $event->sender;

		//	Cache for multi-event speed
		if ( $_schemaFor != get_class( $_model ) )
		{
			$_schema = $_model->getMetaData()->columns;
			$_schemaFor = get_class( $_model );
		}

		//	Not for us? Pass it through...
		if ( isset( $this->_dateFormat[$eventName] ) )
		{
			//	Is it safe?
			if ( ! $_schema )
			{
				$_model->addError(
					self::CLASS_LOG_TAG,
					'Cannot read schema for data formatting'
				);

				return false;
			}

			//	Scoot through and update values...
			/** @var $_column CDbColumnSchema */
			foreach ( $_schema as $_name => $_column )
			{
				if ( ! empty( $_name ) && $_model->hasAttribute( $_name ) && isset( $_schema[$_name], $this->_dateFormat[$eventName][$_column->dbType] ) )
				{
					$_value = $this->_applyFormat( $_column, $_model->getAttribute( $_name ), $eventName );
					$_model->setAttribute( $_name, $_value );
				}
			}
		}

		//	Papa don't preach...
		return parent::$eventName( $event );
	}

	//********************************************************************************
	//* Event Handlers
	//********************************************************************************

	/**
	 * Apply formats before validation
	 * @param CModelEvent $event parameter
	 * @return mixed
	 */
	public function beforeValidate( $event )
	{
		if ( parent::beforeValidate( $event ) )
		{
			return $this->_handleEvent( __METHOD__, $event );
		}
		
		return false;
	}

	/**
	 * Apply any formats
	 * @param CEvent $event
	 * @return mixed
	 */
	public function afterValidate( $event )
	{
		if ( parent::afterValidate( $event ) )
		{
			return $this->_handleEvent( __METHOD__, $event );
		}
		
		return false;
	}

	/**
	 * Apply any formats
	 * @param CEvent $event
	 * @return mixed
	 */
	public function beforeFind( $event )
	{
		if ( parent::beforeFind( $event ) )
		{
			//	Is a default sort defined?
			if ( null !== $this->_defaultSort )
			{
				//	Is a sort defined?
				/** @var $_criteria CDbCriteria */
				$_criteria = $event->sender->getDbCriteria();
					
				//	No sort? Set the default
				if ( ! $_criteria->order )
				{
					$event->sender->getDbCriteria()->mergeWith(
						new CDbCriteria(
							array(
								'order' => $this->_defaultSort,
							)
						)
					);
				}
			}

			return $this->_handleEvent( __METHOD__, $event );
		}
		
		return false;
	}

	/**
	 * Apply any formats
	 * @param CEvent $event
	 * @return mixed
	 */
	public function afterFind( $event )
	{
		return $this->_handleEvent( __METHOD__, $event );
	}

	//*************************************************************************
	//* Properties
	//*************************************************************************

	/**
	 * Sets a format
	 *
	 * @param string $eventName
	 * @param string $formatType
	 * @param string $format
	 * @return xlDataTransformBehavior
	 */
	public function setFormat( $eventName = 'afterValidate', $formatType = 'date', $format = 'm/d/Y' )
	{
		if ( !isset( $this->_dateFormat[$eventName] ) )
			$this->_dateFormat[$eventName] = array( );

		$this->_dateFormat[$eventName][$formatType] = $format;

		return $this;
	}

	/**
	 * Returns the default sort
	 * @return string
	 * @see setDefaultSort
	 */
	public function getDefaultSort()
	{
		return $this->_defaultSort;
	}

	/**
	 * Sets the default sort
	 * @param string $value
	 * @return \xlDataTransformBehavior
	@see getDefaultSort
	 */
	public function setDefaultSort( $value )
	{
		$this->_defaultSort = $value;
		return $this;
	}

	/**
	 * Retrieves a format
	 * @param string $eventName
	 * @param string $formatType
	 * @return string
	 */
	public function getFormat( $eventName = 'afterFind', $formatType = 'date' )
	{
		return XL::nvl( $this->_dateFormat[$eventName][$formatType], 'm/d/Y' );
	}

}