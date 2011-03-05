<?php
/**
 * This file is part of the psYiiExtensions package.
 *
 * @copyright Copyright (c) 2009-2011 Pogostick, LLC.
 * @link http://www.pogostick.com Pogostick, LLC.
 * @license http://www.pogostick.com/licensing
 */

/**
 * Base functionality that I want in ALL helper classes
 *
 * @package 	psYiiExtensions
 * @subpackage 	helpers
 *
 * @author 		Jerry Ablan <jablan@pogostick.com>
 * @version 	SVN: $Id: CPSHelperBase.php 408 2010-11-08 15:57:21Z jerryablan@gmail.com $
 * @since 		v1.0.5
 *
 * @filesource
 */
class CPSHelperBase extends CHtml implements IPSBase
{
	//********************************************************************************
	//* Constants for all components
	//********************************************************************************

	/**
	* Standard output formats
	*/
	const	OF_JSON 		= 0;
	const	OF_HTTP 		= 1;
	const	OF_ASSOC_ARRAY 	= 2;
	const	OF_XML		 	= 3;

	/**
	* Pager locations
	*/
	const 	PL_TOP_LEFT		= 0;
	const 	PL_TOP_RIGHT	= 1;
	const 	PL_BOTTOM_LEFT	= 2;
	const 	PL_BOTTOM_RIGHT	= 3;

	/***
	* Predefined action types for CPSForm
	*/
	const	ACTION_NONE 	= 0;
	const	ACTION_CREATE 	= 1;
	const	ACTION_VIEW 	= 2;
	const	ACTION_EDIT 	= 3;
	const	ACTION_SAVE 	= 4;
	const	ACTION_DELETE 	= 5;
	const	ACTION_ADMIN 	= 6;
	const	ACTION_LOCK 	= 7;
	const	ACTION_UNLOCK 	= 8;

	//	Add your own in between 4 and 997...
	const	ACTION_PREVIEW 		= 996;
	const	ACTION_RETURN 		= 997;
	const	ACTION_CANCEL 		= 998;
	const	ACTION_GENERIC 		= 999;

	//********************************************************************************
	//* Private Members
	//********************************************************************************

	/**
	* Cache the current app for speed
	* @static CWebApplication $thisApp
	*/
	protected static $_thisApp = null;

	/**
	 * Cache the current request
	 * @var CHttpRequest $thisRequest
	 */
	protected static $_thisRequest = null;

	/**
	* Cache the client script object for speed
	* @static CClientScript $clientScript
	*/
	protected static $_clientScript = null;

	/**
	* Cache the user object for speed
	* @static CWebUser $thisUser
	*/
	protected static $_thisUser = null;

	/**
	* Cache the current controller for speed
	* @static CController $thisController
	*/
	protected static $_thisController = null;

	/**
	* Cache the application parameters for speed
	* @static CAttributeCollection $appParameters
	*/
	protected static $_appParameters = null;
	public static function getParams() { self::$_appParameters; }

	/**
	 * An array of class names to search in for missing methods
	 * @static array $classPath
	 */
	protected static $_classPath = array();
	public static function getClassPath() { return self::$_classPath; }
	public static function setClassPath( $arClasses ) { self::$_classPath = $arClasses; }
	public static function addClassToPath( $sClass ) { self::$_classPath[] = $sClass; }

	//********************************************************************************
	//* Public Methods
	//********************************************************************************

	/**
	 * Intialize our private statics
	 */
	public static function init()
	{
		//	Intialize my variables...
		self::$_thisApp = Yii::app();

		//	May or may not be available...
		try { if ( null !== self::$_thisApp ) self::$_clientScript = self::$_thisApp->getClientScript(); } catch ( Exception $_ex ) {}
		try { if ( null !== self::$_thisApp ) self::$_thisUser = self::$_thisApp->getUser(); } catch ( Exception $_ex ) {}
		try { if ( null !== self::$_thisApp ) self::$_thisRequest = self::$_thisApp->getRequest(); } catch ( Exception $_ex ) {}
		try { if ( null !== self::$_thisApp ) self::$_appParameters = self::$_thisApp->params; } catch ( Exception $_ex ) {}
	}

	/**
	* Creates the internal name of a component/widget. Use (@link setInternalName) to change.
	* @param IPSComponent $component
	*/
	public static function createInternalName( IPSComponent $component )
	{
		//	Get the class...
		$_class = get_class( $component );

		//	Set names (with a little YiiXL magic!)
		$_internalName = ( false !== stripos( $_class, 'CPS', 0 ) ) ? str_replace( 'CPS', 'ps', $_class ) : $_class;

		//	Set the names inside the object
		$component->setInternalName( $_internalName );
	}

	/**
	* If value is not set or empty, last passed in argument is returned
	* Allows for multiple nvl chains ( nvl(x,y,z,null) )
	* Since PHP evaluates the arguments before calling a function, this is NOT a short-circuit method.
	*
	* @param mixed
	* @return mixed
	*/
	public static function nvl()
	{
		$_default = null;
		$_args = func_num_args();
		$_haystack = func_get_args();

		for ( $_i = 0; $_i < $_args; $_i++ )
		{
			if ( isset( $_haystack[ $_i ] ) && ! empty( $_haystack[ $_i ] ) )
				return $_haystack[ $_i ];

			$_default = $_haystack[ $_i ];
		}

		return $_default;
	}

	/**
	 * Convenience "in_array" method. Takes variable args.
	 *
	 * The first argument is the needle, the rest are considered in the haystack. For example:
	 *
	 * CPSHelperBase::in( 'x', 'x', 'y', 'z' ) returns true
	 * CPSHelperBase::in( 'a', 'x', 'y', 'z' ) returns false
	 *
	 * @param mixed
	 * @return boolean
	 *
	 */
	public static function in()
	{
		$_haystack = func_get_args();

		if ( count( $_haystack ) > 1 )
		{
			$_needle = array_shift( $_haystack );
			return in_array( $_needle, $_haystack );
		}

		return false;
	}

	/**
	* Returns an analog to Java System.currentTimeMillis()
	*
	* @return integer
	*/
	public static function currentTimeMillis()
	{
		list( $_uSec, $_sec ) = explode( ' ', microtime() );
		return ( ( float )$_uSec + ( float )$_sec );
	}

	/**
	* Similar to {@link PS::o} except it will pull a value from a nested array.
	*
	* @param array $optionList
	* @param integer|string $key
	* @param integer|string $subKey
	* @param mixed $defaultValue
	* @param boolean $unsetValue
	* @return mixed
	*/
	public static function oo( &$optionList = array(), $key, $subKey, $defaultValue = null, $unsetValue = false )
	{
		return PS::o( PS::o( $optionList, $key, array() ), $subKey, $defaultValue, $unsetValue );
	}

	/**
	* Alias for {@link CPSHelperBase::getOption)
	*
	* @param array $optionList
	* @param integer|string $key
	* @param integer|string $subKey
	* @param mixed $defaultValue
	* @param boolean $unsetValue
	* @return mixed
	* @access public
	* @static
	* @see CPSHelperBase::getOption
	*/
	public static function o( &$optionList = array(), $key, $defaultValue = null, $unsetValue = false )
	{
		$_newValue = $defaultValue;

		if ( is_array( $optionList ) )
		{
			if ( ! array_key_exists( $key, $optionList ) )
			{
				//	Ignore case and look...
			    $_newKey = strtolower( $key );

			    foreach ( $optionList as $_key => $_value )
			    {
		    		if ( strtolower( $_key ) == $_newKey )
		    		{
		    			//	Set correct key and break
		    			$key = $_key;
		    			break;
					}
				}
	        }

			if ( isset( $optionList[$key] ) )
			{
				$_newValue = $optionList[$key];
				if ( $unsetValue ) unset( $optionList[$key] );
			}

			//	Set it in the array if not an unsetter...
			if ( ! $unsetValue ) $optionList[$key] = $_newValue;
		}

		//	Return...
		return $_newValue;
	}

	/**
	* Retrieves an option from the given array.
	* $defaultValue is set and returned if $key is not 'set'. Optionally will unset option in array.
	*
	* @param array $optionList
	* @param string $key
	* @param mixed $defaultValue
	* @param boolean $unsetValue
	* @return mixed
	* @access public
	* @static
	*/
	public static function getOption( &$optionList = array(), $key, $defaultValue = null, $unsetValue = false )
	{
		return self::o( $optionList, $key, $defaultValue, $unsetValue );
	}

	/**
	* Sets an option in the given array. Alias of {@link CPSHelperBase::setOption}
	* @param array $optionList
	* @param string $key
	* @param mixed $value
	* @return mixed The new value of the key
	* @static
	*/
	public static function so( array &$optionList, $key, $value = null )
	{
		return $optionList[$key] = $value;
	}

	/**
	* Sets an option in the given array
	*
	* @param array $optionList
	* @param string $key
	* @param mixed $value
	* @return mixed The new value of the key
	* @static
	*/
	public static function setOption( array &$optionList, $key, $value = null )
	{
		return self::so( $optionList, $key, $value );
	}

	/**
	* Unsets an option in the given array. Alias of {@link CPSHelperBase::unsetOption}
	*
	* @param array $optionList
	* @param string $key
	* @return mixed The last value of the key
	* @static
	*/
	public static function uo( array &$optionList, $key )
	{
		return self::o( $optionList, $key, null, true );
	}

	/**
	* Unsets an option in the given array
	*
	* @param array $optionList
	* @param string $key
	* @return mixed The last value of the key
	* @static
	*/
	public static function unsetOption( array &$optionList, $key )
	{
		return self::uo( $optionList, $key );
	}

	/**
	* Merges an array without overwriting. Accepts multiple array arguments
	* If an index exists in the target array, it is appended to the value.
	* @return array
	*/
	public static function smart_array_merge()
	{
		$_iCount = func_num_args();
		$_arResult = array();

		for ( $_i = 0; $_i < $_iCount; $_i++ )
		{
			foreach ( func_get_arg( $_i ) as $_key => $_newValue )
			{
				if ( isset( $_arResult[ $_key ] ) ) $_newValue = $_arResult[ $_key ] . ' ' . $_newValue;
				$_arResult[ $_key ] = $_newValue;
			}
		}

		return $_arResult;
	}

	/**
	 * Make an HTTP request
	 *
	 * @param string $url The URL to call
	 * @param string $sQueryString The query string to attach
	 * @param string $method The HTTP method to use. Can be 'GET' or 'SET'
	 * @param mixed $sNewAgent The custom user method to use. Defaults to 'Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.0; .NET CLR 2.0.50727; .NET CLR 3.0.04506; InfoPath.3)'
	 * @param integer $iTimeOut The number of seconds to wait for a response. Defaults to 60 seconds
	 * @return mixed The data returned from the HTTP request or null for no data
	 */
	public static function makeHttpRequest( $url, $sQueryString = null, $method = 'GET', $sNewAgent = null, $iTimeOut = 60 )
	{
		//	Our user-agent string
		$_sAgent = PS::nvl( $sNewAgent, 'Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.0; .NET CLR 2.0.50727; .NET CLR 3.0.04506; InfoPath.3)' );

		//	Our return results
		$_sResult = null;
		$_payload = $sQueryString;

		//	Convert array to KVPs...
		if ( is_array( $sQueryString ) )
		{
			$_payload = null;

			foreach ( $sQueryString as $_key => $_value )
				$_payload .= "&{$_key}={$_value}";
		}

		// Use CURL if installed...
		if ( function_exists( 'curl_init' ) )
		{
			$_oCurl = curl_init();
			curl_setopt( $_oCurl, CURLOPT_RETURNTRANSFER, true );
			curl_setopt( $_oCurl, CURLOPT_FAILONERROR, true );
			curl_setopt( $_oCurl, CURLOPT_USERAGENT, $_sAgent );
			curl_setopt( $_oCurl, CURLOPT_TIMEOUT, 60 );
			curl_setopt( $_oCurl, CURLOPT_FOLLOWLOCATION, true );
			curl_setopt( $_oCurl, CURLOPT_URL, $url . ( 'GET' == $method ? ( ! empty( $_payload ) ? "?" . trim( $_payload, '&' ) : '' ) : '' ) );

			//	If this is a post, we have to put the post data in another field...
			if ( 'POST' == $method )
			{
				curl_setopt( $_oCurl, CURLOPT_URL, $url );
				curl_setopt( $_oCurl, CURLOPT_POST, true );
				curl_setopt( $_oCurl, CURLOPT_POSTFIELDS, $_payload );
			}

			$_sResult = curl_exec( $_oCurl );
			curl_close( $_oCurl );
		}
		else
			throw new Exception( '"libcurl" is required to use this functionality. Please reconfigure your php.ini to include "libcurl".' );

		return $_sResult;
	}

	/**
	* Parse HTML field for a tag...
	*
	* @param string $sData
	* @param string $sTag
	* @param string $sTagEnd
	* @param integer $iStart Defaults to 0
	* @param string $sNear
	* @return string
	*/
	public static function suckTag( $sData, $sTag, $sTagEnd, $iStart = 0, $sNear = null )
	{
		$_sResult = "";
		$_l = strlen( $sTag );

		//	If near value given, get position of that as start
		if ( $sNear != null )
		{
			$_iStart = stripos( $sData, $sNear, $iStart );
			if ( $_iStart >= 0 )
				$iStart = $_iStart + strlen( $sNear );
		}

		$_i = stripos( $sData, $sTag, $iStart );
		$_k = strlen( $sTagEnd );

		if ( $_i !== false )
		{
			$_j = stripos( $sData, $sTagEnd, $_i + $_l );

			if ( $_j >= 0 )
			{
				$iStart = $_i;
				$_sResult = substr( $sData, $_i + $_l,  $_j - $_i - $_l );
			}

			return( trim( $_sResult ) );
		}

		return( null );
	}

	/**
	* Checks to see if the passed in data is an Url
	*
	* @param string $sData
	* @return boolean
	*/
	public static function isUrl( $sData )
	{
		return( ( @parse_url( $sData ) ) ? TRUE : FALSE );
	}

	/**
	 * Generates a span or div wrapped hyperlink tag.
	 *
	 * @param string $sText The link body. It will NOT be HTML-encoded. Therefore you can pass in HTML code such as an image tag.
	 * @param string $url The Url of the link
	 * @param string $sWrapperId The "id" of the created wrapper
	 * @param array $arHtmlOptions Additional HTML attributes. Besides normal HTML attributes, a few special attributes are also recognized (see {@link clientChange} for more details.)
	 * @param string $sClass The optional class of the created span
	 * @param boolean $bUseDiv If true, a <div> tag will be used instead of a <span>
	 * @return string The generated hyperlink
	 */
	public static function wrappedLink( $sText, $url = '#', $sWrapperId = null, $arHtmlOptions = array(), $sClass = null, $bUseDiv = false )
	{
		return( '<' .
			( $bUseDiv ? 'div' : 'span' ) .
			( null != $sWrapperId ? ' id="' . $sWrapperId . '"' : '' ) .
			( null != $sClass ? ' class="' . $sClass . '"' : '' ) . '>' .
			CHtml::link( $sText, $url, $arHtmlOptions ) .
			'</' . ( $bUseDiv ? 'div' : 'span' ) . '>'
		);
	}

	/**
	* Checks for an empty variable.
	*
	* Useful because the PHP empty() function cannot be reliably used with overridden __get methods.
	*
	* @param mixed $oVar
	* @return bool
	*/
	public static function isEmpty( $oVar )
	{
		return empty( $oVar );
	}

	/**
	* Converts an array to Xml
	*
	* @param mixed $arData The array to convert
	* @param mixed $sRootNodeName The name of the root node in the returned Xml
	* @param string $sXml The converted Xml
	*/
	public static function arrayToXml( $arData, $sRootNodeName = 'data', $sXml = null )
	{
		// turn off compatibility mode as simple xml doesn't like it
		if ( 1 == ini_get( 'zend.ze1_compatibility_mode' ) )
			ini_set( 'zend.ze1_compatibility_mode', 0 );

		if ( null == $sXml )
			$sXml = simplexml_load_string( "<?xml version='1.0' encoding='utf-8'?><{$sRootNodeName} />" );

		// loop through the data passed in.
		foreach ( $arData as $_key => $_newValue )
		{
			// no numeric keys in our xml please!
			if ( is_numeric($_key ) )
				$_key = "unknownNode_". ( string )$_key;

			// replace anything not alpha numeric
			$_key = preg_replace( '/[^a-z]/i', '', $_key );

			// if there is another array found recrusively call this function
			if ( is_array( $_newValue ) )
			{
				$_oNode = $sXml->addChild( $_key );
				self::arrayToXml( $_newValue, $sRootNodeName, $_oNode );
			}
			else
			{
				// add single node.
				$_newValue = htmlentities( $_newValue );
				$sXml->addChild( $_key, $_newValue );
			}
		}

		return( $sXml->asXML() );
	}

	/**
	* Returns the Url of the currently loaded page.
	* @return string
	*/
	public static function getCurrentPageUrl()
	{
		$_bSSL = ( isset( $_SERVER[ 'HTTPS' ] ) && $_SERVER[ 'HTTPS' ] == 'on' );
		return 'http' . ( ( $_bSSL ) ? 's' : '' ) . '://' . $_SERVER[ "SERVER_NAME" ] . ( ( $_SERVER[ "SERVER_PORT" ] != "80" ) ? ":" . $_SERVER[ "SERVER_PORT" ] : '' ) .  $_SERVER[ "REQUEST_URI" ];
	}

	/**
	* Go out and pull the {@link http://gravatar.com/ gravatar} url for the specificed email address.
	*
	* @access public
	* @static
	* @param string $sEmailAddress
	* @param integer $iSize The size of the image to return from 1px to 512px. Defaults to 80
	* @param string $sRating The rating of the image to return: G, PG, R, or X. Defaults to G
	* @since psYiiExtensions v1.0.4
	*/
	public static function getGravatarUrl( $sEmailAddress, $iSize = 80, $sRating = 'g' )
	{
		$sRating = strtolower( $sRating{0} );
		$iSize = intval( $iSize );
		if ( $iSize < 1 || $iSize > 512 ) throw new CPSException( '"$iSize" parameter is out of bounds. Must be between 1 and 512.' );
		if ( ! in_array( $sRating, array( 'g', 'pg', 'r', 'x' ) ) ) throw new CPSException( '"$sRating" parameter must be either "G", "PG", "R", or "X".' );

		return "http://www.gravatar.com/avatar/" . md5( strtolower( $sEmailAddress ) ) . ".jpg?s={$iSize}&r={$sRating}";
	}

	/**
	* Takes parameters and returns an array of the values.
	*
	* @param string|array $oData,... One or more values to read and put into the return array.
	* @return array
	*/
	public static function makeArray( $oData )
	{
		$_arOut = array();
		$_iCount = func_num_args();

		for ( $_i = 0; $_i < $_iCount; $_i++ )
		{
    		//	Any other columns to touch?
    		if ( null !== ( $_oArg = func_get_arg( $_i ) ) )
    		{
    			if ( ! is_array( $_oArg ) )
    				$_arOut[] = $_oArg;
    			else
    			{
    				foreach ( $_oArg as $_value )
    					$_arOut[] = $_value;
				}
			}
		}

		//	Return the fresh array...
		return $_arOut;
	}

	/**
	 * Takes the arguments and makes a file path out of them.
	 * @param mixed File path parts
	 * @return string
	 */
	public static function makePath()
	{
		$_haystack = func_get_args();
		return implode( DIRECTORY_SEPARATOR, $_haystack );
	}

	/**
	 * Multidimensional array search.
	 *
	 * @param <type> $arHaystack
	 * @param <type> $arNeedle
	 * @param <type> $arResult
	 * @param <type> $arPath
	 * @param <type> $sCurrentKey
	 */
	public static function array_search( $arHaystack, $arNeedle, &$arResult, &$arPath = null, $sCurrentKey = '')
	{
		if ( is_array( $arHaystack ) )
		{
			$_iCount = count( $arHaystack );
			$_i = 0;

			foreach ( $arHaystack as $_key => $_oStraw )
			{
				$_bNext = ( ++$_i == $_iCount ) ? false : true;
				if ( is_array( $_oStraw ) ) $arPath[ $_key ] = $_key;
				self::array_search( $_oStraw, $arNeedle, $arResult, $arPath, $_key );
				if (!$_bNext) unset( $arPath[ $currentKey ] );
			}
		}
		else
		{
			$_oStraw = $arHaystack;

			if ( $_oStraw == $arNeedle )
			{
				if ( ! isset( $arPath ) )
					$_sPath = "\$arResult[$sCurrentKey] = \$arNeedle;";
				else
					$_sPath = "\$arResult['".join("']['",$arPath)."'][$sCurrentKey] = \$arNeedle;";

				eval( $_sPath );
			}
		}
	}

	//********************************************************************************
	//* Yii Convenience Mappings
	//********************************************************************************

	/**
	 * Shorthand version of Yii::app()
	 * @return CApplication the application singleton, null if the singleton has not been created yet.
	 */
	public static function _a()
	{
		return self::$_thisApp;
	}

	/**
	 * Convenience method returns the current app name
	 * @see CWebApplication::name
	 * @see CHtml::encode
	 * @return string
	 */
	public static function getAppName( $notEncoded = false ) { return self::_gan( $notEncoded ); }
	public static function _gan( $notEncoded = false )
	{
		return $notEncoded ? self::_a()->name : self::encode( self::_a()->name );
	}

	/**
	 * Convienice method returns the current page title
	 * @see CController::pageTitle
	 * @see CHtml::encode
	 * @return string
	 */
	public static function getPageTitle( $notEncoded = false ) { return self::_gpt( $notEncoded ); }
	public static function _gpt( $notEncoded = false )
	{
		return $notEncoded ? self::_gc()->getPageTitle() : self::encode( self::_gc()->getPageTitle() );
	}

	/**
	 * Convienice methond Returns the base url of the current app
	 * @see CWebApplication::getBaseUrl
	 * @see CHttpRequest::getBaseUrl
	 * @return string
	 */
	public static function getBaseUrl( $absolute = false ) { return self::$_thisApp->getBaseUrl( $absolute ); }
	public static function _gbu( $absolute = false ) { return self::$_thisApp->getBaseUrl( $absolute ); }

	/**
	 * Convienice methond Returns the base path of the current app
	 * @see CWebApplication::getBasePath
	 * @see CHttpRequest::getBasePath
	 * @return string
	 */
	public static function getBasePath() { return self::$_thisApp->getBasePath(); }
	public static function _gbp() { return self::$_thisApp->getBaseUrl(); }

	/***
	 * Retrieves and caches the Yii ClientScript object
	 * @return CClientScript
 	 * @access public
	 * @static
	 */
	public static function getClientScript()
	{
		return self::$_clientScript;
	}

	/**
	* Returns the current clientScript object. Caches for subsequent calls...
	* @return CClientScript
	* @access public
	* @static
	*/
	public static function _cs()
	{
		return self::$_clientScript;
	}

	/**
	 * Terminates the application.
	 * This method replaces PHP's exit() function by calling {@link onEndRequest} before exiting.
	 * @param integer $status exit status (value 0 means normal exit while other values mean abnormal exit).
	 * @param boolean $exit whether to exit the current request. This parameter has been available since version 1.1.5. It defaults to true, meaning the PHP's exit() function will be called at the end of this method.
	 * @access public
	 * @static
	 */
	public static function _end( $status = 0, $exit = true )
	{
		self::$_thisApp->end( $status, $exit );
	}

	/**
	 * @return CDbConnection the database connection
	 */
	public static function getDb() { return self::_db(); }
	public static function _db()
	{
		return self::$_thisApp->getDb();
	}

	/**
	* Registers a javascript file.
	*
	* @param string URL of the javascript file
	* @param integer the position of the JavaScript code. Valid values include the following:
	* <ul>
	* <li>CClientScript::POS_HEAD : the script is inserted in the head section right before the title element.</li>
	* <li>CClientScript::POS_BEGIN : the script is inserted at the beginning of the body section.</li>
	* <li>CClientScript::POS_END : the script is inserted at the end of the body section.</li>
	* </ul>
	* @access public
	* @static
	*/
	public static function registerScriptFile( $url, $ePosition = self::POS_HEAD, $fromPublished = false )
	{
		return self::_rsf( $url, $ePosition, $fromPublished );
	}

	/**
	* Registers a javascript file.
	*
	* @param array|string $urlList Urls of scripts to load. If URL starts with '!', asset library will be prepended. If first character is not a '/', the asset library directory is prepended.
	* @param integer $pagePosition the position of the JavaScript code. Valid values include the following:
	* <ul>
	* <li>CClientScript::POS_HEAD : the script is inserted in the head section right before the title element.</li>
	* <li>CClientScript::POS_BEGIN : the script is inserted at the beginning of the body section.</li>
	* <li>CClientScript::POS_END : the script is inserted at the end of the body section.</li>
	* </ul>
	* @param boolean $fromPublished If true, asset library directory is prepended to url
	* @access public
	* @static
	*/
	public static function _rsf( $urlList, $pagePosition = CClientScript::POS_HEAD, $fromPublished = false )
	{
		if ( ! is_array( $urlList ) ) $urlList = array( $urlList );
		$_prefix = ( $fromPublished ? PS::getExternalLibraryUrl() . DIRECTORY_SEPARATOR : null );

		//	Need external library?
		foreach ( $urlList as $_url )
		{
			if ( $_url[0] != '/' && $fromPublished ) $_url = $_prefix . $_url;

			if ( ! self::$_clientScript->isScriptFileRegistered( $_url ) )
				self::$_clientScript->registerScriptFile( $_url, $pagePosition );
		}
	}

	/**
	* Registers a CSS file
	*
	* @param string URL of the CSS file
	* @param string media that the CSS file should be applied to. If empty, it means all media types.
	* @param boolean If true, asset library directory is prepended to url
	* @access public
	* @static
	*/
	public static function registerCssFile( $url, $media = '', $fromPublished = false )
	{
		return self::_rcf( $url, $media, $fromPublished );
	}

	/**
	* Registers a CSS file
	*
	* @param string URL of the CSS file
	* @param string media that the CSS file should be applied to. If empty, it means all media types.
	* @access public
	* @static
	*/
	public static function _rcf( $urlList, $media = '', $fromPublished = false )
	{
		if ( ! is_array( $urlList ) ) $urlList = array( $urlList );
		$_prefix = ( $fromPublished ? PS::getExternalLibraryUrl() . DIRECTORY_SEPARATOR : null );

		foreach ( $urlList as $_url )
		{
			if ( $_url[0] != '/' && $fromPublished ) $_url = $_prefix . $_url;

			if ( ! self::$_clientScript->isCssFileRegistered( $_url ) )
				self::$_clientScript->registerCssFile( $_url, $media );
		}
	}

	/**
	* Registers a CSS file relative to the current layout directory
	*
	* @param string relative URL of the CSS file
	* @param string media that the CSS file should be applied to. If empty, it means all media types.
	* @access public
	* @static
	*/
	public static function _rlcf( $urlList, $media = '', $fromPublished = false )
	{
		if ( ! is_array( $urlList ) ) $urlList = array( $urlList );
		$_prefix = ( $fromPublished ? Yii::getPathOfAlias('views.layouts') . DIRECTORY_SEPARATOR : null );

		foreach ( $urlList as $_url )
		{
			if ( $_url[0] != '/' && $fromPublished ) $_url = $_prefix . $_url;

			if ( ! self::$_clientScript->isCssFileRegistered( $_url ) )
				self::$_clientScript->registerCssFile( $_url, $media );
		}
	}

	/**
	* Registers a piece of CSS code.
	*
	* @param string ID that uniquely identifies this piece of CSS code
	* @param string the CSS code
	* @param string media that the CSS code should be applied to. If empty, it means all media types.
	* @access public
	* @static
	*/
	public static function registerCss( $sId = null, $sCss, $media = '' )
	{
		return self::_rc( $sId, $sCss, $media );
	}

	/**
	* Registers a piece of CSS code.
	*
	* @param string ID that uniquely identifies this piece of CSS code
	* @param string the CSS code
	* @param string media that the CSS code should be applied to. If empty, it means all media types.
	* @access public
	* @static
	*/
	public static function _rc( $sId = null, $sCss, $media = '' )
	{
		if ( ! self::$_clientScript->isCssRegistered( $sId ) )
			return self::$_clientScript->registerCss( PS::nvl( $sId, CPSWidgetHelper::getWidgetId() ), $sCss, $media );
	}

	/**
	* Registers a piece of javascript code.
	*
	* @param string ID that uniquely identifies this piece of JavaScript code
	* @param string the javascript code
	* @param integer the position of the JavaScript code. Valid values include the following:
	* <ul>
	* <li>CClientScript::POS_HEAD : the script is inserted in the head section right before the title element.</li>
	* <li>CClientScript::POS_BEGIN : the script is inserted at the beginning of the body section.</li>
	* <li>CClientScript::POS_END : the script is inserted at the end of the body section.</li>
	* <li>CClientScript::POS_LOAD : the script is inserted in the window.onload() function.</li>
	* <li>CClientScript::POS_READY : the script is inserted in the jQuery's ready function.</li>
	* </ul>
	* @access public
	* @static
	*/
	public static function registerScript( $sId = null, $sScript, $ePosition = CClientScript::POS_READY )
	{
		return self::_rs( $sId, $sScript, $ePosition );
	}

	/**
	* Registers a piece of javascript code.
	*
	* @param string ID that uniquely identifies this piece of JavaScript code
	* @param string the javascript code
	* @param integer the position of the JavaScript code. Valid values include the following:
	* <ul>
	* <li>CClientScript::POS_HEAD : the script is inserted in the head section right before the title element.</li>
	* <li>CClientScript::POS_BEGIN : the script is inserted at the beginning of the body section.</li>
	* <li>CClientScript::POS_END : the script is inserted at the end of the body section.</li>
	* <li>CClientScript::POS_LOAD : the script is inserted in the window.onload() function.</li>
	* <li>CClientScript::POS_READY : the script is inserted in the jQuery's ready function.</li>
	* </ul>
	* @access public
	* @static
	*/
	public static function _rs( $sId = null, $sScript, $ePosition = CClientScript::POS_READY )
	{
		if ( ! self::$_clientScript->isScriptRegistered( $sId ) )
			self::$_clientScript->registerScript( PS::nvl( $sId, CPSWidgetHelper::getWidgetId() ), $sScript, $ePosition );
	}

	/**
	* Registers a meta tag that will be inserted in the head section (right before the title element) of the resulting page.
	*
	* @param string content attribute of the meta tag
	* @param string name attribute of the meta tag. If null, the attribute will not be generated
	* @param string http-equiv attribute of the meta tag. If null, the attribute will not be generated
	* @param array other options in name-value pairs (e.g. 'scheme', 'lang')
	* @access public
	* @static
	*/
	public static function registerMetaTag( $sContent, $sName = null, $sHttpEquiv = null, $optionList = array() )
	{
		return self::_rmt( $sContent, $sName, $sHttpEquiv, $optionList );
	}

	/**
	* Registers a meta tag that will be inserted in the head section (right before the title element) of the resulting page.
	*
	* @param string content attribute of the meta tag
	* @param string name attribute of the meta tag. If null, the attribute will not be generated
	* @param string http-equiv attribute of the meta tag. If null, the attribute will not be generated
	* @param array other options in name-value pairs (e.g. 'scheme', 'lang')
	* @access public
	* @static
	*/
	public static function _rmt( $sContent, $sName = null, $sHttpEquiv = null, $optionList = array() )
	{
		self::$_clientScript->registerMetaTag( $sContent, $sName, $sHttpEquiv, $optionList );
	}

	/**
	 * Creates a relative URL based on the given controller and action information.
	 * @param string the URL route. This should be in the format of 'ControllerID/ActionID'.
	 * @param array additional GET parameters (name=>value). Both the name and value will be URL-encoded.
	 * @param string the token separating name-value pairs in the URL.
	 * @return string the constructed URL
	 */
	public static function _cu( $route, $options = array(), $ampersand = '&' )
	{
		return self::$_thisApp->createUrl( $route, $options, $ampersand );
	}

	/**
	 * Returns the current request. Equivalent of {@link CApplication::getRequest}
	 * @see CApplication::getRequest
	 * @return CHttpRequest
	 */
	public static function getRequest() { return self::_gr(); }
	/**
	 * Returns the current request. Equivalent of {@link CApplication::getRequest}
	 * @see CApplication::getRequest
	 * @return CHttpRequest
	 */
	public static function _gr()
	{
		return self::$_thisRequest;
	}

	/**
	 * Returns the current user. Equivalent of {@link CWebApplication::getUser}
	 * @see CWebApplication::getUser
	 * @return CUserIdentity
	 */
	public static function getUser() { return self::_gu(); }
	public static function _gu()
	{
		return self::$_thisUser;
	}

	/**
	 * Returns the currently logged in user
	 * @return CWebUser
	 */
	public static function getCurrentUser() { return self::_gcu(); }
	public static function _gcu() { return self::_gs( 'currentUser' ); }

	/**
	 * Returns boolean indicating if user is logged in or not
	 * @return boolean
	 */
	public static function isGuest() { return self::_ig(); }
	public static function _ig() { return self::_gu()->isGuest; }

	/**
	 * Returns application parameters or default value if not found
	 * @see CModule::getParams
	 * @see CModule::setParams
	 * @return mixed
	 */
	public static function getParam( $paramName, $defaultValue = null ) { return self::_gp( $paramName, $defaultValue ); }
	public static function _gp( $paramName, $defaultValue = null )
	{
		if ( self::$_appParameters && self::$_appParameters->contains( $paramName ) )
			return self::$_appParameters->itemAt( $paramName );

		return $defaultValue;
	}

	/**
	 * @return CController the currently active controller
	 * @see CWebApplication::getController
	 */
	public static function getController() { return self::_gc(); }
	public static function _gc()
	{
		return ( null === self::$_thisController ? self::$_thisController = self::$_thisApp->getController() : self::$_thisController );
	}

	/**
	 * @return CComponent The component, if found
	 * @see CWebApplication::getComponent
	 */
	public static function getComponent( $id, $createIfNull = true ) { return self::_gco( $id, $createIfNull ); }
	public static function _gco( $id, $createIfNull = true )
	{
		return self::$_thisApp->getComponent( $id, $createIfNull );
	}

	/**
	 * Convenience access to CAssetManager::publish()
	 *
	 * Publishes a file or a directory.
	 * This method will copy the specified asset to a web accessible directory
	 * and return the URL for accessing the published asset.
	 * <ul>
	 * <li>If the asset is a file, its file modification time will be checked
	 * to avoid unnecessary file copying;</li>
	 * <li>If the asset is a directory, all files and subdirectories under it will
	 * be published recursively. Note, in this case the method only checks the
	 * existence of the target directory to avoid repetitive copying.</li>
	 * </ul>
	 * @param string the asset (file or directory) to be published
	 * @param boolean whether the published directory should be named as the hashed basename.
	 * If false, the name will be the hashed dirname of the path being published.
	 * Defaults to false. Set true if the path being published is shared among
	 * different extensions.
	 * @param integer level of recursive copying when the asset is a directory.
	 * Level -1 means publishing all subdirectories and files;
	 * Level 0 means publishing only the files DIRECTLY under the directory;
	 * level N means copying those directories that are within N levels.
	 * @return string an absolute URL to the published asset
	 * @throws CException if the asset to be published does not exist.
	 * @see CAssetManager::publish
	 */
	public static function _publish( $path , $hashByName = false, $level = -1 )
	{
		return self::$_thisApp->getAssetManager()->publish( $path, $hashByName, $level );
	}

	/**
	 * Performs a redirect. See {@link CHttpRequest::redirect}
	 *
	 * @param string $url
	 * @param boolean $terminate
	 * @param int $statusCode
	 * @see CHttpRequest::redirect
	 */
	public static function redirect( $url, $terminate = true, $statusCode = 302 )
	{
		self::$_thisRequest->redirect( $url, $terminate, $statusCode );
	}

	/**
	 * Returns the value of a variable that is stored in the user session.
	 *
	 * This function is designed to be used by CWebUser descendant classes to
	 * store additional user information the user's session. A variable, if
	 * stored in the session using {@link _ss} can be retrieved back using this
	 * function.
	 *
	 * @param string variable name
	 * @param mixed default value
	 * @return mixed the value of the variable. If it doesn't exist in the session, the provided default value will be returned
	 * @see _ss
	 * @see CWebUser::setState
	 */
	public static function _gs( $stateName, $defaultValue = null )
	{
		$_user = self::_gu();
		return ( null !== $_user ? $_user->getState( $stateName, $defaultValue ) : null );
	}

	/**
	 * Alternative to {@link CWebUser::getState} that takes an array of key parts and assembles them into a hashed key
	 * @param array Array of key parts
	 * @param mixed default value
	 * @return mixed the value of the variable. If it doesn't exist in the session, the provided default value will be returned
	 * @see _ss
	 * @see _gs
	 * @see CWebUser::setState
	 */
	public static function _ghs( $stateKeyParts, $defaultValue = null )
	{
		return self::_gs( CPSHash::hash( implode( '.', $stateKeyParts ) ), $defaultValue );
	}

	/**
	 * Returns a flash message.
	 * A flash message is available only in the current and the next requests.
	 * @param string $key key identifying the flash message
	 * @param mixed $defaultValue value to be returned if the flash message is not available.
	 * @param boolean $delete whether to delete this flash message after accessing it.
	 * Defaults to true. This parameter has been available since version 1.0.2.
	 * @return mixed the message message
	 * @see _sf
	 */
	public function _gf( $key, $defaultValue = null, $delete = true )
	{
		$_user = self::_gu();
		return ( null !== $_user ? $_user->getFlash( $key, $defaultValue, $delete ) : null );
	}

	/**
	 * Stores a variable from the user session
	 *
	 * This function is designed to be used by CWebUser descendant classes
	 * who want to store additional user information in user session.
	 * By storing a variable using this function, the variable may be retrieved
	 * back later using {@link _gs}. The variable will be persistent
	 * across page requests during a user session.
	 *
	 * @param string variable name
	 * @param mixed variable value
	 * @param mixed default value. If $value === $defaultValue (i.e. null), the variable will be removed from the session
	 * @see _gs
	 * @see CWebUser::getState
	 */
	public static function _ss( $stateName, $stateValue, $defaultValue = null )
	{
		$_user = self::_gu();
		return ( null !== $_user ? $_user->setState( $stateName, $stateValue, $defaultValue ) : false );
	}

	/**
	 * Alternative to {@link CWebUser::setState} that takes an array of key parts and assembles them into a hashed key
	 * @param array array of key parts
	 * @param mixed variable value
	 * @param mixed default value
	 * @see _ss
	 * @see _gs
	 * @see CWebUser::setState
	 */
	public static function _shs( $stateKeyParts, $stateValue, $defaultValue = null )
	{
		return self::_ss( CPSHash::hash( implode( '.', $stateKeyParts ) ), $stateValue, $defaultValue );
	}

	/**
	 * Stores a flash message.
	 * A flash message is available only in the current and the next requests.
	 * @param string $key key identifying the flash message
	 * @param mixed $value flash message
	 * @param mixed $defaultValue if this value is the same as the flash message, the flash message
	 * will be removed. (Therefore, you can use setFlash('key',null) to remove a flash message.)
	 * @see {@link CPSHelperBase#_gf}
	 */
	public static function _sf( $key, $value, $defaultValue = null )
	{
		if ( null !== ( $_user = self::_gu() ) ) $_user->setFlash( $key, $value, $defaultValue );
	}

	/**
	 * Returns the details about the error that is currently being handled.
	 * The error is returned in terms of an array, with the following information:
	 * <ul>
	 * <li>code - the HTTP status code (e.g. 403, 500)</li>
	 * <li>type - the error type (e.g. 'CHttpException', 'PHP Error')</li>
	 * <li>message - the error message</li>
	 * <li>file - the name of the PHP script file where the error occurs</li>
	 * <li>line - the line number of the code where the error occurs</li>
	 * <li>trace - the call stack of the error</li>
	 * <li>source - the context source code where the error occurs</li>
	 * </ul>
	 * @return array the error details. Null if there is no error.
	 */
	public static function _ge()
	{
		return self::_a()->getErrorHandler()->getError();
	}

	/**
	 * Creates and returns a CDbCommand object from the specified SQL
	 *
	 * @param string $sql
	 * @return CDbCommand
	 */
	public static function _sql( $sql, $dbToUse = null )
	{
		if ( null !== ( $_db = PS::nvl( $dbToUse, self::$_thisApp->getDb() ) ) )
			return $_db->createCommand( $sql );

		return null;
	}

	/**
	 * Executes the given sql statement and returns all results
	 * @param string $sql
	 * @param array $parameterList List of parameters for call
	 * @param CDbConnection $dbToUse
	 * @return mixed
	 */
	public static function _sqlAll( $sql, $parameterList = array(), $dbToUse = null )
	{
		if ( null !== ( $_db = PS::nvl( $dbToUse, self::$_thisApp->getDb() ) ) )
			return $_db->createCommand( $sql )->queryAll( true, $parameterList );

		return null;
	}

	/**
	 * Executes the given sql statement and returns the first column of all results in an array
	 * @param string $sql
	 * @param array $parameterList List of parameters for call
	 * @param CDbConnection $dbToUse
	 * @return array
	 */
	public static function _sqlAllScalar( $sql, $parameterList = null, $dbToUse = null )
	{
		$_resultList = null;

		if ( null !== ( $_db = PS::nvl( $dbToUse, self::$_thisApp->getDb() ) ) )
		{
			if ( null !== ( $_rowList = $_db->createCommand( $sql )->queryAll( true, $parameterList ) ) )
			{
				$_resultList = array();

				foreach ( $_rowList as $_row )
					$_resultList[] = $_row[0];
			}
		}

		return $_resultList;
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
			$_path = str_replace( $_SERVER['DOCUMENT_ROOT'], '', $_path ) . $url;

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

	//********************************************************************************
	//* Filter Helpers
	//********************************************************************************

	/**
	 * Filters an int optionally returns null
	 * @param mixed $value
	 * @param boolean $nullIfZero
	 * @param integer $min
	 * @param integer $max
	 * @return integer Filtered value or false on error
	 */
	public static function filterInt( $value, $nullIfZero = true, $min = null, $max = null )
	{
		$_value = false;

		if ( false !== ( $_value = filter_var( $value, FILTER_SANITIZE_NUMBER_INT ) ) )
		{
			if ( null !== $min && $_value < $min )
				return false;

			if ( null !== $max && $_value > $max )
				return false;

			if ( $nullIfZero && 0 == $_value )
				return null;
		}

		return $_value;
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
	 * @param array $options
	 * @return mixed
	 */
	public static function __callStatic( $method, $options )
	{
		foreach ( self::$_classPath as $_class )
		{
			if ( method_exists( $_class, $method ) )
				return call_user_func_array( $_class . '::' . $method, $options );
		}
	}

	/**
	 * Serializer that can handle SimpleXmlElement objects
	 * @param mixed $value
	 * @return mixed
	 */
	protected static function _serialize( $value )
	{
		try
		{
			if ( $value instanceof SimpleXMLElement || $value instanceof Util_SpXmlElement )
				return $value->asXML();

			if ( is_object( $value ) )
				return serialize( $value );
		}
		catch ( Exception $_ex )
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
				if ( $value instanceof SimpleXMLElement || $value instanceof Util_SpXmlElement )
					return simplexml_load_string( $value );

				return unserialize( $value );
			}
		}
		catch ( Exception $_ex )
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

}

/**
 * Call our init method to populate our privates...
 */
CPSHelperBase::init();
