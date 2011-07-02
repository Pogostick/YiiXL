<?php
/**
 * This file is part of YiiXL
 * Copyright (c) 2009-2011, Pogostick, LLC. All rights reserved.
 *
 * @link http://www.pogostick.com Pogostick, LLC.
 * @license http://www.pogostick.com/licensing
 * @author Jerry Ablan <yiixl@pogostick.com>
 *
 * @since v1.0.0
 *
 * @package yiixl
 * @subpackage core.components.config
 *
 * @filesource
 */

/**
 * xlConfig provides a property-based configuration settings object.
 * The settings are read-only by default. This can be overridden
 * at construction. Also implemented are Countable and Iterator
 * so the object can be manipulated an an array; IteratorAggregate so
 * it can be used with foreach(); and finally,implements the necessary
 * groundwork for configuration streams.
 *
 * @property bool $readOnly
 */
class xlConfig extends xlComponent implements xlIStreamable, \Countable, \Iterator
{
	//********************************************************************************
	//* Constants
	//********************************************************************************
	
	/**
	 * Our logging tag
	 */
	const
		CLASS_LOG_TAG = 'yiixl.core.components.config.xlConfig'
	;
	
	//********************************************************************************
	//* Member Variables
	//********************************************************************************

	/**
	 * If true, configuration settings cannot be changed once loaded
	 * @var boolean 
	 */
	protected $_readOnly = true;
	/**
	 * Iteration index
	 * @var integer
	 */
	protected $_iterationIndex = 0;
	/**
	 * Holds the number of settings we have
	 * @var integer
	 */
	protected $_optionCount;
	/**
	 * Used when unsetting values during iteration to ensure we do not skip
	 * the next element
	 *
	 * @var boolean
	 */
	protected $_skipNext;
	/**
	 * The last error message
	 * @var string
	 */
	protected $_lastError = null;

	//********************************************************************************
	//* Public Methods
	//********************************************************************************

	/**
	 * Constructor
	 * @param array $options
	 * @param boolean $readOnly
	 */
	public function __construct( array $options = array(), $readOnly = true )
	{
		//	Do not pass $options to parent, we are hijacking...
		parent::__construct();

		$this->_readOnly = $readOnly;
		$this->_iterationIndex = 0;

		//	Now process...
		foreach ( $this->_options as $_key => $_value )
		{
			if ( is_array( $_value ) )
				$this->_options[$_key] = new self( $_value, $readOnly );
			else
				$this->_options[$_key] = $_value;
		}

		//	Set our count...
		$this->_optionCount = count( $this->_options );
	}

	/**
	 * Retrieve a value from the options collection
	 * @param string $key
	 * @param mixed $defaultValue
	 * @param bool $unsetAfter If true, option will be removed after it is returned.
	 * @return mixed
	 */
	public function getOption( $key, $defaultValue = null, $unsetAfter = false )
	{
		return XL::o( $this->_options, $key, $defaultValue, $unsetAfter );
	}

	/**
	 * Return an associative array of all settings
	 * @return array
	 */
	public function toArray()
	{
		/** @var xlConfig $_value */
		$_options = array();
		
		foreach ( $this->_options as $_key => $_value )
		{
			if ( $_value instanceof xlConfig )
				$_options[$_key] = $_value->toArray();
			else
				$_options[$_key] = $_value;
		}
		
		return $_options;
	}

	/**
	 * Merge another xlConfig with this one. The items in $mergeOptions will
	 * override the same named items in the current collection.
	 * @param xlConfig $mergeConfig
	 * @return xlConfig
	 */
	public function merge( xlConfig $mergeConfig )
	{
		foreach ( $mergeConfig as $_key => $_option )
		{
			if ( array_key_exists( $_key, $this->_options ) )
			{
				if ( $_option instanceof xlConfig && $this->{$_key} instanceof xlConfig )
					$this->{$_key} = $this->{$_key}->merge( new xlConfig( $_option->toArray(), $this->_readOnly ) );
				else
					$this->{$_key} = $_option;
			}
			else
			{
				/** @var xlConfig $_option */
				if ( $_option instanceof xlConfig )
					$this->$_key = new xlConfig( $_option->toArray(), $this->_readOnly );
				else
					$this->$_key = $_option;
			}
		}

		return $this;
	}

	/**
	 * Returns the JSON representation of a value
	 * @link http://php.net/manual/en/function.json-encode.php
	 * @param int $jsonOptions Bitmask consisting of JSON_HEX_QUOT, JSON_HEX_TAG, JSON_HEX_AMP, JSON_HEX_APOS,
	 * JSON_FORCE_OBJECT.
	 * @return string a JSON encoded string on success.
	 */
	public function jsonEncode( $jsonOptions = null )
	{
		return json_encode( $this->toArray(), $jsonOptions );
	}
	
	/**
	 * Decodes a JSON string
	 * @link http://php.net/manual/en/function.json-decode.php
	 * @param string $json <p>
	 * The json string being decoded.
	 * </p>
	 * @param bool $assoc [optional] <p>
	 * When true, returned objects will be converted into
	 * associative arrays.
	 * </p>
	 * @param int $depth [optional] <p>
	 * User specified recursion depth.
	 * </p>
	 * @return mixed the value encoded in json in appropriate
	 * PHP type. Values true, false and
	 * null (case-insensitive) are returned as true, false
	 * and &null; respectively. &null; is returned if the
	 * json cannot be decoded or if the encoded
	 * data is deeper than the recursion limit.
	 */
	public function jsonDecode( $json, $assoc = null, $depth = null )
	{
		return json_decode( $json, $assoc, $depth );
	}
	
	//********************************************************************************
	//* Interface Requirements
	//********************************************************************************

	/**
	 * Required by Countable interface
	 * @return int
	 */
	public function count()
	{
		return $this->_optionCount;
	}

	/**
	 * Required by Iterator interface
	 * @return mixed
	 */
	public function current()
	{
		$this->_skipNext = false;
		return current( $this->_options );
	}

	/**
	 * Required by Iterator interface
	 * @return mixed
	 */
	public function key()
	{
		return key( $this->_options );
	}

	/**
	 * Required by Iterator interface
	 */
	public function next()
	{
		if ( $this->_skipNext )
		{
			$this->_skipNext = false;
			return;
		}

		next( $this->_options );
		
		$this->_iterationIndex++;
	}

	/**
	 * Required by Iterator interface
	 */
	public function rewind()
	{
		$this->_skipNext = false;
		reset( $this->_options );
		$this->_iterationIndex = 0;
	}

	/**
	 * Required by Iterator interface
	 * @return boolean
	 */
	public function valid()
	{
		return ( $this->_iterationIndex < $this->_optionCount );
	}

	//********************************************************************************
	//* Magic Method Override
	//********************************************************************************

	/**
	 * Check our options before we pass the buck
	 * @param string $key
	 * @return mixed
	 */
	public function __get( $key )
	{
		if ( array_key_exists( $key, $this->_options ) )
			return $this->getOption( $key );
	
		return parent::__get( $key );
	}

	/**
	 * Only allow setting of a property if not read-only.
	 * @param string $key
	 * @param mixed $value
	 * @throws xlReadOnlyException
	 * @return xlConfig
	 */
	public function __set( $key, $value )
	{
		if ( ! array_key_exists( $key, $this->_options ) )
			return parent::__set( $key, $value );
		
		if ( $this->_readOnly )
		{
			throw new xlReadOnlyException(
				XL::t(
					self::CLASS_LOG_TAG,
					'Property "{class}.{property}" is read only.',
					array(
						'{class}' => get_class( $this ),
						'{property}' => $key,
					)
				)
			);
		}

		if ( is_array( $value ) )
			$this->_options[$key] = new self( $value, $this->_readOnly );
		else
			$this->_options[$key] = $value;

		$this->_optionCount = count( $this->_options );
		
		return $this;
	}

	/**
	 * Deep copy of this collection.
	 * @return void
	 */
	public function __clone()
	{
		$_options = array();
		
		foreach ( $this->_options as $_key => $_value )
		{
			if ( $_value instanceof xlConfig )
				$_options[$_key] = clone $_value;
			else
				$_options[$_key] = $_value;
		}

		$this->_options = $_options;
	}

	/**
	 * @param string $key
	 * @return boolean
	 */
	public function __isset( $key )
	{
		if ( ! array_key_exists( $key, $this->_options ) )
			return parent::__isset( $key );
		
		return isset( $this->_options[$key] );
	}

	/**
	 * @param  string $key
	 * @throws xlReadOnlyException
	 * @return xlConfig
	 */
	public function __unset( $key )
	{
		if ( $this->_readOnly )
		{
			throw new xlReadOnlyException(
				XL::t(
					self::CLASS_LOG_TAG,
					'Property "{class}.{property}" is read only.',
					array(
						'{class}' => get_class( $this ),
						'{property}' => $key,
					)
				)
			);
		}
			
		XL::uo( $this->_options, $key );
		$this->_optionCount = count( $this->_options );
		$this->_skipNext = true;
		
		//	Never break the chain
		return $this;
	}

	//********************************************************************************
	//* Property Accessors
	//********************************************************************************

	/**
	 * Set the read-only flag
	 * @param bool $value
	 * @return xlConfig
	 */
	public function setReadOnly( $value = true )
	{
		$this->_readOnly = $value;

		foreach ( $this->_options as $_key => $_value )
		{
			/** @var xlConfig $_value */
			if ( $_value instanceof xlConfig )
				$_value->setReadOnly( $value );
		}
		
		return $this;
	}

	/**
	 * Gets the read-only flag
	 * @return boolean
	 */
	public function getReadOnly()
	{
		return $this->_readOnly;
	}

}