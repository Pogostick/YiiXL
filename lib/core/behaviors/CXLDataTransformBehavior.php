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
 * CXLDataTransformBehavior
 *
 * If attached to a model, fields are transformed per your configuration.
 * Also provides a default sort for a model
 */
class CXLDataTransformBehavior extends CXLActiveRecordBehavior
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
	//* Property Accessors
	//********************************************************************************

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

	/**
	 * Sets a format
	 *
	 * @param string $eventName
	 * @param string $formatType
	 * @param string $format
	 * @return $this
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
	 * @param string $sValue
	 * @return $this
	 * @see getDefaultSort
	 */
	public function setDefaultSort( $sValue )
	{
		$this->_defaultSort = $sValue;
		return $this;
	}

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
					$_result = date( $this->getFormat( $eventName, $column->dbType ), strtotime( $value ) );
				break;
		}

		return $_result;
	}

	/**
	 * Process the data and apply formats
	 * @param string $eventName
	 * @param CEvent $event
	 */
	protected function handleEvent( $eventName, CEvent $event )
	{
		static $_schema;
		static $_schemaFor;

		$_model = $event->sender;

		//	Cache for multi event speed
		if ( $_schemaFor != get_class( $_model ) )
		{
			$_schema = $_model->getMetaData()->columns;
			$_schemaFor = get_class( $_model );
		}

		//	Not for us? Pass it through...
		if ( isset( $this->_dateFormat[$eventName] ) )
		{
			//	Is it safe?
			if ( !$_schema )
			{
				$_model->addError( __CLASS__, 'Cannot read schema for data formatting' );
				return false;
			}

			//	Scoot through and update values...
			foreach ( $_schema as $_name => $_column )
			{
				if ( !empty( $_name ) && $_model->hasAttribute( $_name ) && isset( $_schema[$_name], $this->_dateFormat[$eventName][$_column->dbType] ) )
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
	 * @addtogroup core_events Events
	 * @brief These are the events utilized by YiiXL
	 * @{
	 */
	/**
	 * 	@defgroup core_events_behavior Behavior Events
	 * 	@brief These events are handled or triggered by behaviors
	 * 	@{
	 */

	/**
	 * Apply formats before validation
	 * @param CModelEvent $event parameter
	 * @return mixed
	 */
	public function beforeValidate( $event )
	{
		return $this->handleEvent( __FUNCTION__, $event );
	}

	/**
	 * Apply any formats
	 * @param CEvent $event
	 * @return mixed
	 */
	public function afterValidate( $event )
	{
		return $this->handleEvent( __FUNCTION__, $event );
	}

	/**
	 * Apply any formats
	 * @param CEvent $event
	 * @return mixed
	 */
	public function beforeFind( $event )
	{
		//	Is a default sort defined?
		if ( null !== $this->_defaultSort )
		{
			//	Is a sort defined?
			$_criteria = $event->sender()->getDbCriteria();

			//	No sort? Set the default
			if ( !$_criteria->order )
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

		return $this->handleEvent( __FUNCTION__, $event );
	}

	/**
	 * Apply any formats
	 * @param CEvent $event
	 */
	public function afterFind( $event )
	{
		return $this->handleEvent( __FUNCTION__, $event );
	}

	/**
	 * 	@}
	 * @}
	 */
}