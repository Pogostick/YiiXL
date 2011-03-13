<?php
/**
 * This file is part of the YiiXL package.
 *
 * @copyright Copyright (c) 2009-2011 Pogostick, LLC.
 * @link http://www.pogostick.com Pogostick, LLC.
 * @license http://www.pogostick.com/licensing
 * @package yiixl
 * @subpackage core
 *
 * @filesource
 * @author 		Jerry Ablan <jablan@pogostick.com>
 */

//	Our core imports
Yii::import( 'yiixl.core.*' );
Yii::import( 'yiixl.core.components.interfaces', true );
Yii::import( 'yiixl.core.exceptions.CXLException', true );
Yii::import( 'yiixl.core.helpers.CXLOptions' );

//	Requirements
require_once dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'CXLComponent.php';

/**
 * YiiXLBase
 *
 * The Mother Of All YiiXL Classes!
 */
class YiiXLBase extends YiiBase implements IXLUIHelper, IXLLogger
{
	//********************************************************************************
	//* Constants
	//********************************************************************************

	/**
	 * Our logging tag
	 */
	const	CLASS_LOG_TAG = 'yiixl.core.YiiXLBase';

	//********************************************************************************
	//* Private Members
	//********************************************************************************

	/**
	 * Cache the current app for speed
	 * @staticvar CWebApplication $thisApp
	 */
	protected static $_thisApp = null;
	/**
	 * Cache the current request
	 * @staticvar CHttpRequest $thisRequest
	 */
	protected static $_thisRequest = null;
	/**
	 * Cache the client script object for speed
	 * @staticvar CClientScript $clientScript
	 */
	protected static $_clientScript = null;
	/**
	 * Cache the user object for speed
	 * @staticvar CWebUser $thisUser
	 */
	protected static $_thisUser = null;
	/**
	 * Cache the current controller for speed
	 * @staticvar CController $thisController
	 */
	protected static $_thisController = null;
	/**
	 * @staticvar array $_validLogLevels Our valid log levels based on interface definition
	 */
	protected static $_validLogLevels;
	/**
	 * A static ID counter for generating unique names
	 * @staticvar integer $_uniqueIdCounter
	 */
	protected static $_uniqueIdCounter = 1000;
	/**
	 * Cache the application parameters for speed
	 * @staticvar CAttributeCollection $appParameters
	 */
	protected static $_appParameters = null;
	/**
	 * Returns the cached copy of the configured application parameters {@see CWebApplication::getParams}
	 * @return array
	 */
	public static function getParams()
	{
		return self::$_appParameters;
	}

	/**
	 * An array of class names to search in for missing static methods.
	 * This is a quick an dirty little polymorpher.
	 *
	 * @staticvar array $classPath
	 */
	protected static $_classPath = array(
		'CXLOptions',
		'CHtml',
	);

	public static function getClassPath()
	{
		return self::$_classPath;
	}

	public static function setClassPath( $classPath )
	{
		self::$_classPath = $classPath;
	}

	public static function addClassToPath( $className )
	{
		self::$_classPath[] = self::import( $className );
	}

	//********************************************************************************
	//* Public Methods
	//********************************************************************************

	/**
	 * Intialize our private statics
	 */
	public static function initialize( $options = array() )
	{
		//	Intialize my variables...
		self::$_thisApp = Yii::app();

		//	Individually, each of these may or may not be available...
		try
		{
			if ( null !== self::$_thisApp )
				self::$_clientScript = self::$_thisApp->getClientScript();
		}
		catch ( CXLException $_ex )
		{
		}

		try
		{
			if ( null !== self::$_thisApp )
				self::$_thisUser = self::$_thisApp->getUser();
		}
		catch ( CXLException $_ex )
		{
		}

		try
		{
			if ( null !== self::$_thisApp )
				self::$_thisRequest = self::$_thisApp->getRequest();
		}
		catch ( CXLException $_ex )
		{
		}

		try
		{
			if ( null !== self::$_thisApp )
				self::$_appParameters = self::$_thisApp->getParams();
		}
		catch ( CXLException $_ex )
		{
		}

		//	Add our logging class to the class path
		self::addClassToPath( 'yiixl.core.helpers.CXLHash' );
		self::addClassToPath( 'yiixl.core.helpers.CXLLogHelper' );
	}

	/**
	 * Constructs a unique name based on component, hashes by default
	 * @param IXLComponent $component
	 * @param boolean $humanReadable If true, names returned will not be hashed
	 * @return string
	 */
	public static function createUniqueName( IXLComponent $component, $humanReadable = false )
	{
		$_name = get_class( $component ) . '.' . self::$_uniqueIdCounter++;
		return 'yiixl.' . ( $humanReadable ? $_name : CXLHash::hash( $_name ) );
	}

	/**
	 * NVL = Null VaLue. Copycat function from PL/SQL. Pass in a list of arguments and the first non-null
	 * item is returned. Good for setting default values, etc. Last non-null value in list becomes the
	 * new "default value".
	 *
	 * NOTE: Since PHP evaluates the arguments before calling a function, this is NOT a short-circuit method.
	 *
	 * @param mixed
	 * @return mixed
	 */
	public static function nvl( /* Polymorphic */ )
	{
		$_defaultValue = null;

		foreach ( func_get_args() as $_argument )
		{
			if ( null === $_argument ) return $_argument;
			$_defaultValue = $_argument;
		}

		return $_defaultValue;
	}

	/**
	 * Convenience "in_array" method. Takes variable args.
	 *
	 * The first argument is the needle, the rest are considered in the haystack. For example:
	 *
	 * CXLHelperBase::in( 'x', 'x', 'y', 'z' ) returns true
	 * CXLHelperBase::in( 'a', 'x', 'y', 'z' ) returns false
	 *
	 * @param mixed
	 * @return boolean
	 *
	 */
	public static function in()
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
		return ( ( float ) $_uSeconds + ( float ) $_seconds );
	}

	/**
	 * Alias for {@link CXLHelperBase::o)
	 *
	 * @param array $options
	 * @param string $key
	 * @param mixed $defaultValue
	 * @param boolean $unsetValue
	 * @return mixed
	 * @access public
	 * @static
	 */
	public static function getOption( &$options = array( ), $key, $defaultValue = null, $unsetValue = false )
	{
		return self::o( $options, $key, $defaultValue, $unsetValue );
	}

	/**
	 * Retrieves an option from the given array. $defaultValue is set and returned if $key is not 'set'.
	 * Optionally will unset option in array.
	 *
	 * @param array $options
	 * @param integer|string $key
	 * @param integer|string $subKey
	 * @param mixed $defaultValue
	 * @param boolean $unsetValue
	 * @return mixed
	 * @access public
	 * @static
	 * @see CXLHelperBase::getOption
	 */
	public static function o( &$options = array( ), $key, $defaultValue = null, $unsetValue = false )
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
				if ( $unsetValue ) unset( $options[$key] );
			}

			//	Set it in the array if not an unsetter...
			if ( !$unsetValue ) $options[$key] = $_newValue;
		}

		//	Return...
		return $_newValue;
	}

	/**
	 * Similar to {@link PS::o} except it will pull a value from a nested array.
	 *
	 * @param array $options
	 * @param integer|string $key
	 * @param integer|string $subKey
	 * @param mixed $defaultValue Only applies to target value
	 * @param boolean $unsetValue Only applies to target value
	 * @return mixed
	 */
	public static function oo( &$options = array( ), $key, $subKey, $defaultValue = null, $unsetValue = false )
	{
		return PS::o( PS::o( $options, $key, array( ) ), $subKey, $defaultValue, $unsetValue );
	}

	/**
	 * Alias for {@link CXLHelperBase::so}
	 *
	 * @param array $options
	 * @param string $key
	 * @param mixed $value
	 * @return mixed The new value of the key
	 * @static
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
	 * @static
	 */
	public static function so( array &$options, $key, $value = null )
	{
		return $options[$key] = $value;
	}

	/**
	 * Alias of {@link CXLHelperBase::unsetOption}
	 * @param array $options
	 * @param string $key
	 * @return mixed The last value of the key
	 * @static
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
	 * @static
	 */
	public static function uo( array &$options, $key )
	{
		return self::o( $options, $key, null, true );
	}

	/**
	 * Takes parameters and returns an array of the values. Keys are maintained.
	 * @param mixed Zero or more values to read and put into the return array.
	 * @return array
	 */
	public static function makeArray( /* Polymorphic */ )
	{
		$_newArray = array();

		foreach ( func_get_args() as $_key => $_argument )
			$_newArray[$_key] = $_argument;

		//	Return the fresh array...
		return $_newArray;
	}

	/**
	 * Takes the arguments and makes a file path out of them. No leading or trailing
	 * separator is added.
	 * @param mixed File path parts
	 * @return string
	 */
	public static function makePath( /* Polymorphic */ )
	{
		return implode( DIRECTORY_SEPARATOR, func_get_args() );
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
	public static function getAppName( $notEncoded = false )
	{
		return self::_gan( $notEncoded );
	}

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
	public static function getPageTitle( $notEncoded = false )
	{
		return self::_gpt( $notEncoded );
	}

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
	public static function getBaseUrl( $absolute = false )
	{
		return self::$_thisRequest->getBaseUrl( $absolute );
	}

	public static function _gbu( $absolute = false )
	{
		return self::$_thisRequest->getBaseUrl( $absolute );
	}

	/**
	 * Convienice methond Returns the base path of the current app
	 * @see CWebApplication::getBasePath
	 * @see CHttpRequest::getBasePath
	 * @return string
	 */
	public static function getBasePath()
	{
		return self::$_thisApp->getBasePath();
	}

	public static function _gbp()
	{
		return self::$_thisApp->getBaseUrl();
	}

	/*	 * *
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
	public static function getDb()
	{
		return self::_db();
	}

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
		if ( !is_array( $urlList ) ) $urlList = array( $urlList );
		$_prefix = ( $fromPublished ? PS::getExternalLibraryUrl() . DIRECTORY_SEPARATOR : null );

		//	Need external library?
		foreach ( $urlList as $_url )
		{
			if ( $_url[0] != '/' && $fromPublished ) $_url = $_prefix . $_url;

			if ( !self::$_clientScript->isScriptFileRegistered( $_url ) ) self::$_clientScript->registerScriptFile( $_url, $pagePosition );
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
		if ( !is_array( $urlList ) ) $urlList = array( $urlList );
		$_prefix = ( $fromPublished ? PS::getExternalLibraryUrl() . DIRECTORY_SEPARATOR : null );

		foreach ( $urlList as $_url )
		{
			if ( $_url[0] != '/' && $fromPublished ) $_url = $_prefix . $_url;

			if ( !self::$_clientScript->isCssFileRegistered( $_url ) ) self::$_clientScript->registerCssFile( $_url, $media );
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
		if ( !is_array( $urlList ) ) $urlList = array( $urlList );
		$_prefix = ( $fromPublished ? Yii::getPathOfAlias( 'views.layouts' ) . DIRECTORY_SEPARATOR : null );

		foreach ( $urlList as $_url )
		{
			if ( $_url[0] != '/' && $fromPublished ) $_url = $_prefix . $_url;

			if ( !self::$_clientScript->isCssFileRegistered( $_url ) ) self::$_clientScript->registerCssFile( $_url, $media );
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
	public static function registerCss( $css, $options = array() )
	{
		$_media = self::o( $options, 'media', 'screen' );

		if ( null === ( $_id = self::o( $options, 'media' ) ) )
			$_id = self::createUniqueName( $css );

		return self::_rc( $_id, $css, $media );
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
		if ( !self::$_clientScript->isCssRegistered( $sId ) ) return self::$_clientScript->registerCss( PS::nvl( $sId, CPSWidgetHelper::getWidgetId() ), $sCss, $media );
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
		if ( !self::$_clientScript->isScriptRegistered( $sId ) ) self::$_clientScript->registerScript( PS::nvl( $sId, CPSWidgetHelper::getWidgetId() ), $sScript, $ePosition );
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
	public static function registerMetaTag( $sContent, $sName = null, $sHttpEquiv = null, $options = array( ) )
	{
		return self::_rmt( $sContent, $sName, $sHttpEquiv, $options );
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
	public static function _rmt( $sContent, $sName = null, $sHttpEquiv = null, $options = array( ) )
	{
		self::$_clientScript->registerMetaTag( $sContent, $sName, $sHttpEquiv, $options );
	}

	/**
	 * Creates a relative URL based on the given controller and action information.
	 * @param string the URL route. This should be in the format of 'ControllerID/ActionID'.
	 * @param array additional GET parameters (name=>value). Both the name and value will be URL-encoded.
	 * @param string the token separating name-value pairs in the URL.
	 * @return string the constructed URL
	 */
	public static function _cu( $route, $options = array( ), $ampersand = '&' )
	{
		return self::$_thisApp->createUrl( $route, $options, $ampersand );
	}

	/**
	 * Returns the current request. Equivalent of {@link CApplication::getRequest}
	 * @see CApplication::getRequest
	 * @return CHttpRequest
	 */
	public static function getRequest()
	{
		return self::_gr();
	}

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
	public static function getUser()
	{
		return self::_gu();
	}

	public static function _gu()
	{
		return self::$_thisUser;
	}

	/**
	 * Returns the currently logged in user
	 * @return CWebUser
	 */
	public static function getCurrentUser()
	{
		return self::_gcu();
	}

	public static function _gcu()
	{
		return self::_gs( 'currentUser' );
	}

	/**
	 * Returns boolean indicating if user is logged in or not
	 * @return boolean
	 */
	public static function isGuest()
	{
		return self::_ig();
	}

	public static function _ig()
	{
		return self::_gu()->isGuest;
	}

	/**
	 * Returns application parameters or default value if not found
	 * @see CModule::getParams
	 * @see CModule::setParams
	 * @return mixed
	 */
	public static function getParam( $paramName, $defaultValue = null )
	{
		return self::_gp( $paramName, $defaultValue );
	}

	public static function _gp( $paramName, $defaultValue = null )
	{
		if ( self::$_appParameters && self::$_appParameters->contains( $paramName ) ) return self::$_appParameters->itemAt( $paramName );

		return $defaultValue;
	}

	/**
	 * @return CController the currently active controller
	 * @see CWebApplication::getController
	 */
	public static function getController()
	{
		return self::_gc();
	}

	public static function _gc()
	{
		return ( null === self::$_thisController ? self::$_thisController = self::$_thisApp->getController() : self::$_thisController );
	}

	/**
	 * @return CComponent The component, if found
	 * @see CWebApplication::getComponent
	 */
	public static function getComponent( $id, $createIfNull = true )
	{
		return self::_gco( $id, $createIfNull );
	}

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
	public static function _publish( $path, $hashByName = false, $level = -1 )
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
	 * @see {@link CXLHelperBase#_gf}
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
		if ( null !== ( $_db = PS::nvl( $dbToUse, self::$_thisApp->getDb() ) ) ) return $_db->createCommand( $sql );

		return null;
	}

	/**
	 * Executes the given sql statement and returns all results
	 * @param string $sql
	 * @param array $parameterList List of parameters for call
	 * @param CDbConnection $dbToUse
	 * @return mixed
	 */
	public static function _sqlAll( $sql, $parameterList = array( ), $dbToUse = null )
	{
		if ( null !== ( $_db = PS::nvl( $dbToUse, self::$_thisApp->getDb() ) ) ) return $_db->createCommand( $sql )->queryAll( true, $parameterList );

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
				$_resultList = array( );

				foreach ( $_rowList as $_row ) $_resultList[] = $_row[0];
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

		if ( false !== $_path && null !== $url ) $_path = str_replace( $_SERVER['DOCUMENT_ROOT'], '', $_path ) . $url;

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
			if ( null !== $min && $_value < $min ) return false;

			if ( null !== $max && $_value > $max ) return false;

			if ( $nullIfZero && 0 == $_value ) return null;
		}

		return $_value;
	}
	
	//********************************************************************************
	//* Magic Methods
	//********************************************************************************

	/**
	 * This is a standardized magic method "helper"
	 * @param CXLComponent $object
	 * @param array $behaviors
	 * @param string $method
	 * @param array $parameters
	 * @return mixed
	 */
	public static function smartCall( CXLComponent $object, $method, $parameters = array() )
	{
		$_methods = $object->getBehaviorMethods();
		
		//	Make sure the function exists
		if ( is_callable( $_methodClass = XL::o( $_methods, $method, null, true ) ) )
		{
			//	Throw myself at the front of the arguments
			$_newParameters = $parameters;
			
			array_unshift( $_newParameters, $object );

			//	And call the method
			return call_user_func_array(
				array(
					$_methodClass,
					$method
				),
				$_newParameters
			);
		}

		//	Otherwise let Yii deal with it...
		return self::smartCallStatic( $object, $method, $parameters );
	}

	/**
	 * Calls a static method in classPath if not found here. Allows you to extend this object
	 * at runtime with additional helpers. Injects $object into parameter list.
	 * @param IXLComponent $object
	 * @param string $method
	 * @param array $parameters
	 * @return mixed
	 * @throws CXLMethodNotFoundException
	 */
	public static function smartCallStatic( IXLComponent $object, $method, $parameters = array() )
	{
		//	Throw myself at the front of the arguments
		$_newParameters = $parameters;

		array_unshift( $_newParameters, $object );

		foreach ( self::$_classPath as $_class )
		{
			$_obj = new ReflectionClass( $_class );
			$_newParameters = ( $_obj->implementsInterface( 'IXLShifter' ) ? $_newParameters : $parameters );
			
			if ( is_callable( array( $_class, $method ) ) )
				return call_user_func_array( $_class . '::' . $method, $_newParameters );
		}

		throw new CXLMethodNotFoundException(
			XL::t(
				self::CLASS_LOG_TAG,
				'Method "{class}.{method}" is read only.',
				array(
					'{class}' => get_class( $object ),
					'{method}' => $method,
				)
			)
		);
	}

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
				return call_user_func_array( $_class . '::' . $method, $parameters );
		}
		
		//	I give, it's all you...
		parent::__callStatic( $method, $parameters );
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
			if ( $value instanceof SimpleXMLElement || $value instanceof Util_SpXmlElement ) return $value->asXML();

			if ( is_object( $value ) ) return serialize( $value );
		}
		catch ( CXLException $_ex )
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
				if ( $value instanceof SimpleXMLElement || $value instanceof Util_SpXmlElement ) return simplexml_load_string( $value );

				return unserialize( $value );
			}
		}
		catch ( CXLException $_ex )
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
		return!( false === $_result && $value != serialize( false ) );
	}

}

//	Initialize our base...
YiiXLBase::initialize();