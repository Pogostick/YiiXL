<?php
/**
 * This file is part of the YiiXL package.
 *
 * @copyright Copyright (c) 2009-2011 Pogostick, LLC.
 * @link http://www.pogostick.com Pogostick, LLC.
 * @license http://www.pogostick.com/licensing
 *
 * @package yiixl
 * @subpackage core.components
 *
 * @author 		Jerry Ablan <jablan@pogostick.com>
 * @since 		v1.0.0
 *
 * @filesource
 */

//	Include Files
//	Constants
//	Global Settings

/**
 * CXLConfig provides a property-based configuration settings object.
 * The settings are read-only by default. This can be overrided
 * at construction. Also implemented are Countable and Iterator 
 * so the object can be manipulated in various ways.
 */
class CXLConfig extends CXLComponent implements Countable, Iterator
{
	//********************************************************************************
	//* Constants
	//********************************************************************************
	
	/**
	 * Our logging tag
	 */
	const	CLASS_LOG_TAG = 'yiixl.core.components.config.CXLConfig';
	
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
	 * Contains array of configuration data
	 * @var array
	 */
	protected $_options;
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
	public function __construct( array $options, $readOnly = true )
	{
		$this->_readOnly = $readOnly;
		$this->_iterationIndex = 0;
		$this->_options = array();
		
		foreach ( $options as $_key => $_value )
		{
			if ( is_array( $_value ) )
				$this->_options[$_key] = new self( $_value, $readOnly );
			else
				$this->_options[$_key] = $_value;
		}

		$this->_optionCount = count( $this->_options );
	}

	/**
	 * Retrieve a value from the options collection
	 * @param string $key
	 * @param mixed $defaultValue
	 * @return mixed
	 */
	public function getOption( $key, $defaultValue = null )
	{
		return XL::o( $key, $defaultValue );
	}

	/**
	 * Return an associative array of all settings
	 * @return array
	 */
	public function toArray()
	{
		$_options = array();
		
		foreach ( $this->_options as $_key => $_value )
		{
			if ( $_value instanceof CXLConfig )
				$_options[$_key] = $_value->toArray();
			else
				$_options[$_key] = $_value;
		}
		
		return $_options;
	}

	/**
	 * Merge another CXLConfig with this one. The items in $mergeOptions will 
	 * override the same named items in the current collection.
	 * @param CXLConfig $mergeConfig
	 * @return CXLConfig
	 */
	public function merge( CXLConfig $mergeConfig )
	{
		foreach ( $mergeConfig as $_key => $_option )
		{
			if ( array_key_exists( $_key, $this->_options ) )
			{
				if ( $_option instanceof CXLConfig && $this->{$_key} instanceof CXLConfig )
					$this->{$_key} = $this->{$_key}->merge( new CXLConfig( $_option->toArray(), $this->_readOnly ) );
				else
					$this->{$_key} = $_option;
			}
			else
			{
				if ( $_option instanceof CXLConfig )
					$this->$_key = new CXLConfig( $_option->toArray(), $this->_readOnly );
				else
					$this->$_key = $_option;
			}
		}

		return $this;
	}

	/**
	 * Returns the JSON representation of a value
	 * @link http://php.net/manual/en/function.json-encode.php
	 * @param mixed $value <p>
	 * The value being encoded. Can be any type except
	 * a resource.
	 * </p>
	 * <p>
	 * This function only works with UTF-8 encoded data.
	 * </p>
	 * @param int $options [optional] <p>
	 * Bitmask consisting of JSON_HEX_QUOT,
	 * JSON_HEX_TAG,
	 * JSON_HEX_AMP,
	 * JSON_HEX_APOS,
	 * JSON_FORCE_OBJECT.
	 * </p>
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
	 * @throws CXLReadOnlyException
	 * @return CXLConfig 
	 */
	public function __set( $key, $value )
	{
		if ( ! array_key_exists( $key, $this->_options ) )
			return parent::__set( $key, $value );
		
		if ( $this->_readOnly )
		{
			throw new CXLReadOnlyException(
				Yii::t(
					self::LOG_TAG,
					'Property "{class}.{property}" is read only.',
					array(
						'{class}' => get_class( $this ),
						'{property}' => $key,
					)
				)
			);
		}

		if ( is_array( $value ) )
			$this->_options[$_key] = new self( $value, $readOnly );
		else
			$this->_options[$_key] = $value;

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
			if ( $_value instanceof CXLConfig )
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
	 * @throws CXLReadOnlyException
	 * @return CXLConfig 
	 */
	public function __unset( $key )
	{
		if ( $this->_readOnly )
		{
			throw new CXLReadOnlyException(
				Yii::t(
					self::LOG_TAG,
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
	 * @return CXLConfig
	 */
	public function setReadOnly( $value = true )
	{
		$this->_readOnly = $value;

		foreach ( $this->_options as $_key => $_value )
		{
			if ( $_value instanceof CXLConfig )
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