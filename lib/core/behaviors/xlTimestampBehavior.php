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
 * xlTimeStampBehavior
 *
 * Allows you to define time stamp fields in models and automatically update them.
 *
 * @property string $createdColumnName The name of the column that holds your create date
 * @property string $createdByColumnName The name of the column that holds your creating user
 * @property string $lastModifiedColumnName The name of the column that holds your last modified date
 * @property string $lastModifiedByColumnName The name of the column that holds your last modifying user
 * @property string $dateTimeFunctionName The name of the function to use to set dates. Defaults to date('Y-m-d H:i:s').
 */
class xlTimeStampBehavior extends xlActiveRecordBehavior
{
	//********************************************************************************
	//* Member Variables
	//********************************************************************************

	/**
	 * @var string $_createdColumnName The optional name of the created column in the table
	 */
	protected $_createdColumnName = null;
	public function getCreatedColumnName() { return $this->_createdColumnName; }
	public function setCreatedColumnName( $sValue ) { $this->_createdColumnName = $sValue; }

	/**
	 * @var string $_createdByColumnName The optional name of the created by user id column in the table
	 */
	protected $_createdByColumnName = null;
	public function getCreatedByColumnName() { return $this->_createdByColumnName; }
	public function setCreatedByColumnName( $sValue ) { $this->_createdByColumnName = $sValue; }

	/**
	 * @var string $_lastModifiedColumnName The optional name of the last modified column in the table
	 */
	protected $_lastModifiedColumnName = null;
	public function getLastModifiedColumnName() { return $this->_lastModifiedColumnName; }
	public function setLastModifiedColumnName( $sValue ) { $this->_lastModifiedColumnName = $sValue; }

	/**
	 * @var string $_lastModifiedByColumnName The optional name of the modified by user id column in the table
	 */
	protected $_lastModifiedByColumnName = null;
	public function getLastModifiedByColumnName() { return $this->_lastModifiedByColumnName; }
	public function setLastModifiedByColumnName( $sValue ) { $this->_lastModifiedByColumnName = $sValue; }

	/**
	* @var string $_dateTimeFunction The date/time function with which to stamp records
	*/
	protected $_dateTimeFunction = null;
	public function getDateTimeFunction() { return $this->_dateTimeFunction; }
	public function setDateTimeFunction( $sValue ) { $this->_dateTimeFunction = $sValue; }

	//********************************************************************************
	//*  Event Handlers
	//********************************************************************************

	/**
	* Timestamps row
	* @param CModelEvent $event
	*/
	public function beforeValidate( $event )
	{
		//	Handle created stamp
		if ( $event->sender->isNewRecord )
		{
			if ( $this->_createdColumnName && $event->sender->hasAttribute( $this->_createdColumnName ) )
				$this->getOwner()->setAttribute( $this->_createdColumnName, ( null === $this->_dateTimeFunction ) ? date('Y-m-d H:i:s') : eval('return ' . $this->_dateTimeFunction . ';') );

			if ( $this->_createdByColumnName && $event->sender->hasAttribute( $this->_createdByColumnName ) && ! $event->sender->getAttribute( $this->_createdByColumnName ) )
				$this->getOwner()->setAttribute( $this->_createdByColumnName, Yii::app()->user->getId() );
		}

		//	Handle lmod stamp
		if ( $this->_lastModifiedColumnName && $event->sender->hasAttribute( $this->_lastModifiedColumnName ) )
				$this->getOwner()->setAttribute( $this->_lastModifiedColumnName, ( null === $this->_dateTimeFunction ) ? date('Y-m-d H:i:s') : eval('return ' . $this->_dateTimeFunction . ';') );

		//	Handle user id stamp
		if ( $this->_lastModifiedByColumnName && $event->sender->hasAttribute( $this->_lastModifiedByColumnName ) && ! $event->sender->getAttribute( $this->_lastModifiedByColumnName ) )
			$this->getOwner()->setAttribute( $this->_lastModifiedByColumnName, Yii::app()->user->getId() );

		return parent::beforeValidate( $event );
	}

	//********************************************************************************
	//* Public Methods
	//********************************************************************************

    /**
    * Returns formatted create/lmod dates for forms
    * @return string 
    */
    public function showDates()
    {
    	if ( ! $this->getOwner()->isNewRecord )
			return XL::showDates( $this->getOwner(), $this->_createdColumnName, $this->_lastModifiedColumnName, 'D M j, Y' );
	}

 	/**
 	* Sets lmod date(s) and saves
 	* Will optionally touch other columns. You can pass in a single column name or an array of columns.
 	* This is useful for updating not only the lmod column but a last login date for example.
 	* Only the columns that have been touched are updated. If no columns are updated, no database action is performed.
 	*
 	* @param mixed $additionalColumns The single column name or array of columns to touch in addition to configured lmod column
 	* @return boolean
 	*/
    public function touch( $additionalColumns = null )
    {
    	$_touchValue = ( null === $this->_dateTimeFunction ) ? date( 'Y-m-d H:i:s' ) : eval( 'return ' . $this->_dateTimeFunction . ';' );
    	$_updateList = array();

    	//	Any other columns to touch?
    	if ( null !== $additionalColumns )
    	{
    		foreach ( XL::makeArray( $additionalColumns ) as $_attribute )
    		{
    			if ( $this->getOwner()->hasAttribute( $_attribute ) )
    			{
    				$this->getOwner()->setAttribute( $_attribute, $_touchValue );
    				$_updateList[] = $_attribute;
				}
    		}
		}

		if ( $this->_lastModifiedColumnName && $this->getOwner()->hasAttribute( $this->_lastModifiedColumnName ) )
		{
			$this->getOwner()->setAttribute( $this->_lastModifiedColumnName, $_touchValue );
    		$_updateList[] = $this->_lastModifiedColumnName;
		}

		//	Only update if and what we've touched...
		return count( $_updateList ) ? $this->getOwner()->update( $_updateList ) : true;
	}

	/**
	 * (PHP 5 &gt;= 5.1.0)<br/>
	 * Notify an observer
	 * @link http://php.net/manual/en/splsubject.notify.php
	 * @return void
	 */
	public function notify()
	{
	}

	/**
	 * Bind a callback to an event
	 * @param string $eventName
	 * @param callback $callback
	 * @return boolean
	 */
	public function bind( $eventName, $callback )
	{
		// TODO: Implement bind() method.
	}

	/**
	 * Unbind from an event
	 * @param string $eventName
	 * @return boolean
	 */
	public function unbind( $eventName )
	{
		// TODO: Implement unbind() method.
	}


}