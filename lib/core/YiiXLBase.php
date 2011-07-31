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
 * @subpackage core
 *
 * @filesource
 */

//	Our core imports
Yii::import( 'yiixl.core.*' );
Yii::import( 'yiixl.core.components.interfaces', true );
Yii::import( 'yiixl.core.exceptions.xlException', true );
Yii::import( 'yiixl.core.helpers.xlOptions' );

//	Require the base component
require_once __DIR__ . '/components/xlComponent.php';

/**
 * YiiXLBase
 *
 * The Mother Of All YiiXL Classes!
 *
 * @property CWebApplication $thisApp
 * @property CHttpRequest $thisRequest
 * @property CWebUser $thisUser
 * @property array $validLogLevels
 * @property CClientScript $clientScript
 * @property integer $uniqueIdCounter
 * @property CAttributeCollection $appParameters
 * @property array $classPath
 * @property integer $debugLevel
 * @property YiiXLBase xl
 * @property YiiXLBase XL
 */
class YiiXLBase extends YiiBase implements xlIDebuggable, xlIForm, xlILog
{
	//********************************************************************************
	//* Constants
	//********************************************************************************

	/**
	 * @const string Our logging tag
	 */
	const
		CLASS_LOG_TAG = 'yiixl.core.YiiXLBase'
	;

	//********************************************************************************
	//* Private Members
	//********************************************************************************

	/**
	 * Cache the current app for speed
	 * @static
	 * @var CWebApplication
	 */
	protected static $_thisApp = null;
	/**
	 * Cache the current request
	 * @static
	 * @var CHttpRequest
	 */
	protected static $_thisRequest = null;
	/**
	 * Cache the client script object for speed
	 * @static
	 * @var CClientScript
	 */
	protected static $_clientScript = null;
	/**
	 * Cache the user object for speed
	 * @static
	 * @var CWebUser
	 */
	protected static $_thisUser = null;
	/**
	 * Cache the current controller for speed
	 * @static
	 * @var CController
	 */
	protected static $_thisController = null;
	/**
	 * Our valid log levels based on interface definition
	 * @static
	 * @var array
	 */
	protected static $_validLogLevels;
	/**
	 * A static ID counter for generating unique names
	 * @static
	 * @var integer
	 */
	protected static $_uniqueIdCounter = 1000;
	/**
	 * Cache the application parameters for speed
	 * @static
	 * @var CAttributeCollection
	 */
	protected static $_appParameters = null;
	/**
	 * An array of class names to search in for missing static methods.
	 * This is a quick an dirty little polymorpher.
	 * @var array
	 * @static
	 */
	protected static $_classPath = array(
		'CHtml',
	);
	/**
	 * @var int
	 * @static
	 */
	protected static $_debugLevel = self::LOG_INFO;

	//********************************************************************************
	//* Public Methods
	//********************************************************************************

	/**
	 * Initialize our private statics
	 * @param array $options
	 */
	public static function initialize( $options = array() )
	{
		//	Initialize my variables...
		self::$_thisApp = Yii::app();

		//	Individually, each of these may or may not be available...
		try
		{
			if ( null !== self::$_thisApp )
			{
				self::$_clientScript = self::$_thisApp->getClientScript();
			}
		}
		catch ( xlException $_ex )
		{
		}

		try
		{
			if ( null !== self::$_thisApp )
			{
				self::$_thisUser = self::$_thisApp->getUser();
			}
		}
		catch ( xlException $_ex )
		{
		}

		try
		{
			if ( null !== self::$_thisApp )
			{
				self::$_thisRequest = self::$_thisApp->getRequest();
			}
		}
		catch ( xlException $_ex )
		{
		}

		try
		{
			if ( null !== self::$_thisApp )
			{
				self::$_appParameters = self::$_thisApp->getParams();
			}
		}
		catch ( xlException $_ex )
		{
		}

		//	Add our logging and hash classes to the class path
		self::addClassToPath( 'yiixl.core.helpers.xlHash' );
		self::addClassToPath( 'yiixl.core.helpers.xlLogHelper' );
		self::addClassToPath( 'yiixl.core.helpers.xlYiiHelper' );
	}

	/**
	 * Constructs a unique name based on component, hashes by default
	 * @param xlIComponent $component
	 * @param boolean $humanReadable If true, names returned will not be hashed
	 * @return string
	 */
	public static function createUniqueName( xlIComponent $component, $humanReadable = false )
	{
		$_name = get_class( $component ) . '.' . self::$_uniqueIdCounter++;
		return 'yiixl.' . ( $humanReadable ? $_name : xlHash::hash( $_name ) );
	}

	/**
	 * NVL = Null VaLue. Copycat function from PL/SQL. Pass in a list of arguments and the first non-null
	 * item is returned. Good for setting default values, etc. Last non-null value in list becomes the
	 * new "default value".
	 * NOTE: Since PHP evaluates the arguments before calling a function, this is NOT a short-circuit method.
	 * @param mixed $_ [optional]
	 * @return mixed
	 */
	public static function nvl( &$_ = null )
	{
		$_defaultValue = null;

		foreach ( func_get_args() as $_argument )
		{
			if ( null === $_argument )
			{
				return $_argument;
			}
			$_defaultValue = $_argument;
		}

		return $_defaultValue;
	}

	/**
	 * Convenience "in_array" method. Takes variable args.
	 * The first argument is the needle, the rest are considered in the haystack. For example:
	 * xlHelperBase::in( 'x', 'x', 'y', 'z' ) returns true
	 * xlHelperBase::in( 'a', 'x', 'y', 'z' ) returns false
	 * @param mixed $_ [optional]
	 * @return boolean
	 */
	public static function in( &$_ = null )
	{
		//	Clever or dumb? Dunno...
		return in_array(
			array_shift( func_get_args() ),
			func_get_args()
		);
	}

	/**
	 * Returns the current microtime in more readable format
	 * @return integer
	 */
	public static function microtime()
	{
		list( $_uSeconds, $_seconds ) = explode( ' ', microtime() );
		return ( ( float )$_uSeconds + ( float )$_seconds );
	}

	/**
	 * Alias for {@link xlHelperBase::o)
	 * @param array $options
	 * @param string $key
	 * @param mixed $defaultValue
	 * @param boolean $unsetValue
	 * @return mixed
	 */
	public static function getOption( &$options = array(), $key, $defaultValue = null, $unsetValue = false )
	{
		return self::o( $options, $key, $defaultValue, $unsetValue );
	}

	/**
	 * Retrieves an option from the given array. $defaultValue is set and returned if $key is not 'set'.
	 * Optionally will unset option in array.
	 *
	 * @param array $options
	 * @param integer|string $key
	 * @param mixed $defaultValue
	 * @param boolean $unsetValue
	 * @return mixed
	 * @see xlHelperBase::getOption
	 */
	public static function o( &$options = array(), $key, $defaultValue = null, $unsetValue = false )
	{
		$_newValue = $defaultValue;

		if ( is_array( $options ) )
		{
			if ( ! array_key_exists( $key, $options ) )
			{
				//	Ignore case and look...
				$_newKey = strtolower( $key );

				foreach ( $options as $_key => $_value )
				{
					if ( strtolower( $_key ) == $_newKey )
					{
						//	Set correct key and break
						$key = $_key;
						break;
					}
				}
			}

			if ( isset( $options[$key] ) )
			{
				$_newValue = $options[$key];

				if ( $unsetValue )
				{
					unset( $options[$key] );
				}
			}

			//	Set it in the array if not an unsetter...
			if ( ! $unsetValue )
			{
				$options[$key] = $_newValue;
			}
		}

		//	Return...
		return $_newValue;
	}

	/**
	 * Similar to {@link YiiXLBase::o} except it will pull a value from a nested array.
	 * @param array $options
	 * @param integer|string $key
	 * @param integer|string $subKey
	 * @param mixed $defaultValue Only applies to target value
	 * @param boolean $unsetValue Only applies to target value
	 * @return mixed
	 */
	public static function oo( &$options = array(), $key, $subKey, $defaultValue = null, $unsetValue = false )
	{
		return self::o( self::o( $options, $key, array() ), $subKey, $defaultValue, $unsetValue );
	}

	/**
	 * Alias for {@link xlHelperBase::so}
	 * @param array $options
	 * @param string $key
	 * @param mixed $value
	 * @return mixed The new value of the key
	 */
	public static function setOption( array &$options, $key, $value = null )
	{
		return self::so( $options, $key, $value );
	}

	/**
	 * Sets an option in the given array.
	 * @param array $options
	 * @param string $key
	 * @param mixed $value Defaults to null
	 * @return mixed The new value of the key
	 */
	public static function so( array &$options, $key, $value = null )
	{
		return $options[$key] = $value;
	}

	/**
	 * Alias of {@link xlHelperBase::unsetOption}
	 * @param array $options
	 * @param string $key
	 * @return mixed The last value of the key
	 */
	public static function unsetOption( array &$options, $key )
	{
		return self::uo( $options, $key );
	}

	/**
	 * Unsets an option in the given array
	 *
	 * @param array $options
	 * @param string $key
	 * @return mixed The last value of the key
	 */
	public static function uo( array &$options, $key )
	{
		return self::o( $options, $key, null, true );
	}

	/**
	 * Takes a list of things and returns them in an array as the values. Keys are maintained.
	 * @param mixed $_ [optional]
	 * @return array
	 */
	public static function makeArray( &$_ = null )
	{
		$_newArray = array();

		foreach ( func_get_args() as $_key => $_argument )
		{
			$_newArray[$_key] = $_argument;
		}

		//	Return the fresh array...
		return $_newArray;
	}

	/**
	 * Takes the arguments and makes a file path out of them. No leading or trailing
	 * separator is added.
	 * @param mixed $_ [optional]
	 * @return string
	 */
	public static function makePath( &$_ = null )
	{
		return implode( DIRECTORY_SEPARATOR, func_get_args() );
	}

	/**
	 * Generic super-easy/lazy way to convert lots of different things (like SimpleXmlElement) to an array
	 * @param object $object
	 * @param int $options JSON decoding options
	 * @return array|false
	 */
	public static function toArray( $object, $options = JSON_HEX_TAG )
	{
		return @json_decode( @json_encode( $object ), $options  );
	}

	/**
	 * Determine if PHP is running CLI mode or not
	 * @return boolean True if currently running in CLI
	 */
	public static function isCLI()
	{
		return ( 'cli' == php_sapi_name() && empty( $_SERVER['REMOTE_ADDR'] ) );
	}

	/**
	 * Create a path alias.
	 * Note, this method neither checks the existence of the path nor normalizes the path.
	 * @param string $alias alias to the path
	 * @param string $path the path corresponding to the alias. If this is null, the corresponding
	 * path alias will be removed.
	 */
	public static function _spoa( $alias, $path )
	{
		Yii::setPathOfAlias( $alias, $path );
	}

	/**
	 * Translates an alias into a file path.
	 * Note, this method does not ensure the existence of the resulting file path.
	 * It only checks if the root alias is valid or not.
	 * @param string $alias alias (e.g. system.web.CController)
	 * @param string $url Additional url combine with alias
	 * @return mixed file path corresponding to the alias, false if the alias is invalid.
	 */
	public static function _gpoa( $alias, $url = null )
	{
		$_path = Yii::getPathOfAlias( $alias );

		if ( false !== $_path && null !== $url )
		{
			$_path = str_replace( $_SERVER['DOCUMENT_ROOT'], '', $_path ) . $url;
		}

		return $_path;
	}

	/**
	 * @return boolean whether this is POST request.
	 */
	public static function isPostRequest()
	{
		return self::_gr()->getIsPostRequest();
	}

	/**
	 * @return boolean True if this is an AJAX (xhr) request.
	 */
	public static function isAjaxRequest()
	{
		return self::_gr()->getIsAjaxRequest();
	}

	/**
	 * Returns the cached copy of the configured application parameters {@see CWebApplication::getParams}
	 * @return array
	 */
	public static function getParams()
	{
		return self::$_appParameters;
	}

	/**
	 * @static
	 * @param string $className
	 * @return boolean Returns true if the class was added, false if it existed already
	 */
	public static function addClassToPath( $className )
	{
		if ( ! in_array( $className, self::$_classPath ) )
		{
			$_alias = XL::_gpoa( $className );

			if ( false !== require_once( $_alias ) )
			{
				self::$_classPath[] = $_alias;
				return true;
			}
		}

		return false;
	}

	//********************************************************************************
	//* Magic Methods
	//********************************************************************************

	/**
	 * Calls a static method in classPath if not found here. Allows you to extend this object
	 * at runtime with additional helpers.
	 *
	 * Only available in PHP 5.3+
	 *
	 * @param string $method
	 * @param array $parameters
	 * @return mixed
	 */
	public static function __callStatic( $method, $parameters )
	{
		foreach ( self::$_classPath as $_class )
		{
			if ( is_callable( array( $_class, $method ) ) )
			{
				return call_user_func_array(
					$_class . '::' .
					$method,
					$parameters
				);
			}
		}

		//	I give, it's all you...
		return parent::__callStatic( $method, $parameters );
	}

	//*************************************************************************
	//* Private Methods
	//*************************************************************************

	/**
	 * Serializer that can handle SimpleXmlElement objects
	 * @param mixed $value
	 * @return mixed
	 */
	protected static function _serialize( $value )
	{
		try
		{
			if ( $value instanceof SimpleXMLElement )
				/** @var SimpleXMLElement $value */
				return $value->asXML();

			if ( is_object( $value ) )
				/** @var stdClass $value */
				return serialize( $value );
		}
		catch ( xlException $_ex )
		{
		}

		return $value;
	}

	/**
	 * Unserializer that can handle SimpleXmlElement objects
	 * @param mixed $value
	 * @return mixed
	 */
	protected static function _unserialize( $value )
	{
		try
		{
			if ( self::_isSerialized( $value ) )
			{
				if ( $value instanceof SimpleXMLElement )
				{
					return simplexml_load_string( $value );
				}

				return unserialize( $value );
			}
		}
		catch ( xlException $_ex )
		{
		}

		return $value;
	}

	/**
	 * Tests if a value needs unserialization
	 * @param mixed $value
	 * @return boolean
	 */
	protected static function _isSerialized( $value )
	{
		$_result = @unserialize( $value );
		return !( false === $_result && $value != serialize( false ) );
	}

	//*************************************************************************
	//* Properties
	//*************************************************************************

	/**
	 * @return array
	 */
	public static function getClassPath()
	{
		return self::$_classPath;
	}

	/**
	 * @static
	 * @param array $classPath
	 * @return array Returns the prior value
	 */
	public static function setClassPath( $classPath = array() )
	{
		$_priorValue = self::$_classPath;
		self::$_classPath = $classPath;
		return $_priorValue;
	}

	/**
	 * Gets the debug level
	 * @return integer
	 */
	public static function getDebugLevel()
	{
		return self::$_debugLevel;
	}

	/**
	 * Sets the debug level
	 * @param integer $debugLevel
	 * @return integer The previous value
	 */
	public static function setDebugLevel( $debugLevel = xlILog::LOG_INFO )
	{
		$_oldLevel = self::$_debugLevel;
		self::$_debugLevel = $debugLevel;
		return $_oldLevel;
	}

	/**
	 * @param $appParameters
	 * @return \CAttributeCollection|null
	 */
	public static function setAppParameters( $appParameters )
	{
		$_old = self::$_appParameters;
		self::$_appParameters = $appParameters;
		return $_old;
	}

	/**
	 * @return null
	 */
	public static function getAppParameters()
	{
		return self::$_appParameters;
	}

	/**
	 * @param $thisApp
	 * @return \CWebApplication|null
	 */
	public static function setThisApp( $thisApp )
	{
		$_old = self::$_thisApp;
		self::$_thisApp = $thisApp;
		return $_old;
	}

	/**
	 * @return null
	 */
	public static function getThisApp()
	{
		return self::$_thisApp;
	}

	/**
	 * @param $thisController
	 * @return \CController|null
	 */
	public static function setThisController( $thisController )
	{
		$_old = self::$_thisController;
		self::$_thisController = $thisController;
		return $_old;
	}

	/**
	 * @return null
	 */
	public static function getThisController()
	{
		return self::$_thisController;
	}

	/**
	 * @param $thisRequest
	 * @return \CHttpRequest|null
	 */
	public static function setThisRequest( $thisRequest )
	{
		$_old = self::$_thisRequest;
		self::$_thisRequest = $thisRequest;
		return $_old;
	}

	/**
	 * @return null
	 */
	public static function getThisRequest()
	{
		return self::$_thisRequest;
	}

	/**
	 * @param $thisUser
	 * @return \CWebUser|null
	 */
	public static function setThisUser( $thisUser )
	{
		$_old = self::$_thisUser;
		self::$_thisUser = $thisUser;
		return $_old;
	}

	/**
	 * @return null
	 */
	public static function getThisUser()
	{
		return self::$_thisUser;
	}

	/**
	 * @param $uniqueIdCounter
	 * @return int
	 */
	public static function setUniqueIdCounter( $uniqueIdCounter )
	{
		$_old = self::$_uniqueIdCounter; 
		self::$_uniqueIdCounter = $uniqueIdCounter;
		return $_old;
	}

	/**
	 * @return int
	 */
	public static function getUniqueIdCounter()
	{
		return self::$_uniqueIdCounter;
	}

	/**
	 * @param $validLogLevels
	 * @return array
	 */
	public static function setValidLogLevels( $validLogLevels )
	{
		$_old = self::$_validLogLevels;
		self::$_validLogLevels = $validLogLevels;
		return $_old;
	}

	/**
	 * @return array
	 */
	public static function getValidLogLevels()
	{
		return self::$_validLogLevels;
	}

}

//	Initialize our base...
YiiXLBase::initialize();

//	And our friendly short-cutter
require_once 'XL.php';