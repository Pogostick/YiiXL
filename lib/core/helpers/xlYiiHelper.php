<?php
/**
 * ${FILE}
 * This file is part of $ProductName$ 
 *
 * @copyright 	Copyright (c) 2009-2011 Pogostick, LLC.
 * @author 		Jerry Ablan <jablan@pogostick.com>
 * @link 		http://www.pogostick.com Pogostick, LLC.
 * @license 	http://www.pogostick.com/licensing/
 *
 * @package 	$PackageBase$
 * @subpackage 	
 *
 * @filesource	
 */

//	Imports
 
/**
 * xlYiiHelper
 */
class xlYiiHelper extends xlBaseHelper
{
	//*************************************************************************
	//* Private Members 
	//*************************************************************************

	/**
	 * Cache the current app for speed
	 * @static
	 * @var CWebApplication
	 */
	protected static $_application = null;
	/**
	 * Cache the current request
	 * @static
	 * @var CHttpRequest
	 */
	protected static $_request = null;
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
	protected static $_user = null;
	/**
	 * Cache the current controller for speed
	 * @static
	 * @var CController
	 */
	protected static $_controller = null;
	/**
	 * Cache the application parameters for speed
	 * @static
	 * @var CAttributeCollection
	 */
	protected static $_applicationParameters = null;

	//*************************************************************************
	//* Public Methods 
	//*************************************************************************

	/**
	 * @static
	 * @return void
	 */
	public static function initialize()
	{
		parent::initialize();

		//	Cache the Yii values for speed
		self::$_application = Yii::app();
		self::$_request = self::$_application->getRequest();
		self::$_clientScript = self::$_application->getClientScript();
		self::$_user = self::$_application->getUser();
		self::$_controller = self::$_application->getController();
		self::$_applicationParameters = self::$_application->getParams();
	}

	//*************************************************************************
	//* Private Methods 
	//*************************************************************************

	//*************************************************************************
	//* Properties
	//*************************************************************************

	//********************************************************************************
	//* Yii Convenience Mappings
	//********************************************************************************

	/**
	 * Shorthand version of Yii::app()
	 * @return CApplication the application singleton
	 */
	public static function app()
	{
		return self::$_application;
	}

	/**
	 * Convenience method returns the current app name
	 * @see CWebApplication::name
	 * @see CHtml::encode
	 * @param bool $notEncoded
	 * @return string
	 */
	public static function getAppName( $notEncoded = false )
	{
		return self::_gan( $notEncoded );
	}

	/**
	 * Convenience method returns the current app name
	 * @param boolean $notEncoded
	 * @return string
	 * @see CWebApplication::name
	 * @see CHtml::encode
	 */
	public static function _gan( $notEncoded = false )
	{
		return $notEncoded ? self::app()->name : self::encode( self::app()->name );
	}

	/**
	 * Convienice method returns the current page title
	 * @see CController::pageTitle
	 * @see CHtml::encode
	 * @param bool $notEncoded
	 * @return string
	 */
	public static function getPageTitle( $notEncoded = false )
	{
		return self::_gpt( $notEncoded );
	}

	/**
	 * @param bool $notEncoded
	 * @return #M#C\YiiXLBase.encode|#M#M#C\YiiXLBase._gc.getPageTitle|? #M#C\YiiXLBase.encode|#M#M#C\YiiXLBase._gc.getPageTitle|?
	 */
	public static function _gpt( $notEncoded = false )
	{
		return $notEncoded ? self::_gc()->getPageTitle() : self::encode( self::_gc()->getPageTitle() );
	}

	/**
	 * Convienice methond Returns the base url of the current app
	 * @see CWebApplication::getBaseUrl
	 * @see CHttpRequest::getBaseUrl
	 * @param bool $absolute
	 * @return string
	 */
	public static function getBaseUrl( $absolute = false )
	{
		return self::$_request->getBaseUrl( $absolute );
	}

	/**
	 * @param bool $absolute
	 * @return #M#P#C\YiiXLBase._request.getBaseUrl|? #M#P#C\YiiXLBase._request.getBaseUrl|?
	 */
	public static function _gbu( $absolute = false )
	{
		return self::$_request->getBaseUrl( $absolute );
	}

	/**
	 * Convienice methond Returns the base path of the current app
	 * @see CWebApplication::getBasePath
	 * @see CHttpRequest::getBasePath
	 * @return string
	 */
	public static function getBasePath()
	{
		return self::$_application->getBasePath();
	}

	/**

	 * @return #M#P#C\YiiXLBase._application.getBaseUrl|? #M#P#C\YiiXLBase._application.getBaseUrl|?
	 */
	public static function _gbp()
	{
		return self::$_application->getBaseUrl();
	}

	/*	 * *
	 * Retrieves and caches the Yii ClientScript object
	 * @return CClientScript
	 * @access public
	 * @static
	 */

	/**
	 * @return null
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
	 * @param boolean $exit whether to exit the current request. This parameter has been available since version 1
	 * .1.5. It defaults to true, meaning the PHP's exit() function will be called at the end of this method.
	 * @access public
	 * @static
	 */
	public static function _end( $status = 0, $exit = true )
	{
		self::$_application->end( $status, $exit );
	}

	/**
	 * @return CDbConnection the database connection
	 */
	public static function getDb()
	{
		return self::_db();
	}

	/**

	 * @return #M#P#C\YiiXLBase._application.getDb|? #M#P#C\YiiXLBase._application.getDb|?
	 */
	public static function _db()
	{
		return self::$_application->getDb();
	}

	/**
	 * Registers a javascript file.
	 *
	 * @param $url
	 * @param #P#C\YiiXLBase.POS_HEAD|? $ePosition
	 * @param bool $fromPublished
	 *
	 *
	 * @internal param $ #P#C\YiiXLBase.POS_HEAD|? $ePosition
	 * @internal param \URL $string of the javascript file
	 *
	 * @internal param \the $integer position of the JavaScript code. Valid values include the following:
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
	 * @param array|string $urlList Urls of scripts to load. If URL starts with '!',
	 * asset library will be prepended. If first character is not a '/', the asset library directory is prepended.
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
		if ( !is_array( $urlList ) )
		{
			$urlList = array($urlList);
		}
		$_prefix = ( $fromPublished ? self::getExternalLibraryUrl() . DIRECTORY_SEPARATOR : null );

		//	Need external library?
		foreach ( $urlList as $_url )
		{
			if ( $_url[0] != '/' && $fromPublished )
			{
				$_url = $_prefix . $_url;
			}

			if ( !self::$_clientScript->isScriptFileRegistered( $_url ) )
			{
				self::$_clientScript->registerScriptFile( $_url, $pagePosition );
			}
		}
	}

	/**
	 * Registers a CSS file
	 *
	 * @param $url
	 * @param string $media
	 * @param bool $fromPublished
	 *
	 * @internal param \URL $string of the CSS file
	 *
	 * @internal param \media $string that the CSS file should be applied to. If empty, it means all media types.
	 *
	 * @internal param \If $boolean true, asset library directory is prepended to url
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
	 * @param $urlList
	 * @param string $media
	 * @param bool $fromPublished
	 *
	 * @internal param \URL $string of the CSS file
	 *
	 * @internal param \media $string that the CSS file should be applied to. If empty, it means all media types.
	 * @access public
	 * @static
	 */
	public static function _rcf( $urlList, $media = '', $fromPublished = false )
	{
		if ( !is_array( $urlList ) )
		{
			$urlList = array($urlList);
		}
		$_prefix = ( $fromPublished ? self::getExternalLibraryUrl() . DIRECTORY_SEPARATOR : null );

		foreach ( $urlList as $_url )
		{
			if ( $_url[0] != '/' && $fromPublished )
			{
				$_url = $_prefix . $_url;
			}

			if ( !self::$_clientScript->isCssFileRegistered( $_url ) )
			{
				self::$_clientScript->registerCssFile( $_url, $media );
			}
		}
	}

	/**
	 * Registers a CSS file relative to the current layout directory
	 *
	 * @param $urlList
	 * @param string $media
	 * @param bool $fromPublished
	 *
	 * @internal param \relative $string URL of the CSS file
	 *
	 * @internal param \media $string that the CSS file should be applied to. If empty, it means all media types.
	 * @access public
	 * @static
	 */
	public static function _rlcf( $urlList, $media = '', $fromPublished = false )
	{
		if ( !is_array( $urlList ) )
		{
			$urlList = array($urlList);
		}
		$_prefix = ( $fromPublished ? Yii::getPathOfAlias( 'views.layouts' ) . DIRECTORY_SEPARATOR : null );

		foreach ( $urlList as $_url )
		{
			if ( $_url[0] != '/' && $fromPublished )
			{
				$_url = $_prefix . $_url;
			}

			if ( !self::$_clientScript->isCssFileRegistered( $_url ) )
			{
				self::$_clientScript->registerCssFile( $_url, $media );
			}
		}
	}

	/**
	 * Registers a piece of CSS code.
	 *
	 * @param $css
	 * @param array $options
	 * @return #M#P#C\YiiXLBase._clientScript.registerCss|? #M#P#C\YiiXLBase._clientScript.registerCss|?@internal param \ID $string that uniquely identifies this piece of CSS
	 * code
	 *
	 * @internal param \the $string CSS code
	 *
	 * @internal param \media $string that the CSS code should be applied to. If empty, it means all media types.
	 * @access public
	 * @static
	 */
	public static function registerCss( $css, $options = array() )
	{
		$_media = self::o( $options, 'media', 'screen' );

		if ( null === ( $_id = self::o( $options, 'media' ) ) )
		{
			$_id = self::createUniqueName( $css );
		}

		return self::_rc( $_id, $css, $_media );
	}

	/**
	 * Registers a piece of CSS code.
	 *
	 * @param null $sId
	 * @param $sCss
	 * @param string $media
	 * @return #M#P#C\YiiXLBase._clientScript.registerCss|? #M#P#C\YiiXLBase._clientScript.registerCss|?@internal param \ID $string that uniquely identifies this piece of CSS
	 * code
	 *
	 * @internal param \the $string CSS code
	 *
	 * @internal param \media $string that the CSS code should be applied to. If empty, it means all media types.
	 * @access public
	 * @static
	 */
	public static function _rc( $sId = null, $sCss, $media = '' )
	{
		if ( !self::$_clientScript->isCssRegistered( $sId ) )
		{
			return self::$_clientScript->registerCss( self::nvl( $sId, xlWidgetHelper::getWidgetId() ), $sCss,
													  $media );
		}
	}

	/**
	 * Registers a piece of javascript code.
	 *
	 * @param null $sId
	 * @param $sScript
	 * @param #P#C\CClientScript.POS_READY|? $ePosition
	 *
	 * @internal param $ #P#C\CClientScript.POS_READY|? $ePosition
	 *
	 * @internal param \ID $string that uniquely identifies this piece of JavaScript code
	 *
	 * @internal param \the $string javascript code
	 *
	 * @internal param \the $integer position of the JavaScript code. Valid values include the following:
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
	 * @param null $sId
	 * @param $sScript
	 * @param #P#C\CClientScript.POS_READY|? $ePosition
	 *
	 * @internal param $ #P#C\CClientScript.POS_READY|? $ePosition
	 *
	 * @internal param \ID $string that uniquely identifies this piece of JavaScript code
	 *
	 * @internal param \the $string javascript code
	 *
	 * @internal param \the $integer position of the JavaScript code. Valid values include the following:
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
		if ( !self::$_clientScript->isScriptRegistered( $sId ) )
		{
			self::$_clientScript->registerScript( self::nvl( $sId, xlWidgetHelper::getWidgetId() ), $sScript,
												  $ePosition );
		}
	}

	/**
	 * Registers a meta tag that will be inserted in the head section (right before the title element) of the
	 * resulting page.
	 *
	 * @param $sContent
	 * @param null $sName
	 * @param null $sHttpEquiv
	 * @param array $options
	 *
	 * @internal param \content $string attribute of the meta tag
	 *
	 * @internal param \name $string attribute of the meta tag. If null, the attribute will not be generated
	 *
	 * @internal param \http $string -equiv attribute of the meta tag. If null, the attribute will not be generated
	 *
	 * @internal param \other $array options in name-value pairs (e.g. 'scheme', 'lang')
	 * @access public
	 * @static
	 */
	public static function registerMetaTag( $sContent, $sName = null, $sHttpEquiv = null, $options = array() )
	{
		return self::_rmt( $sContent, $sName, $sHttpEquiv, $options );
	}

	/**
	 * Registers a meta tag that will be inserted in the head section (right before the title element) of the
	 * resulting page.
	 *
	 * @param $sContent
	 * @param null $sName
	 * @param null $sHttpEquiv
	 * @param array $options
	 *
	 * @internal param \content $string attribute of the meta tag
	 *
	 * @internal param \name $string attribute of the meta tag. If null, the attribute will not be generated
	 *
	 * @internal param \http $string -equiv attribute of the meta tag. If null, the attribute will not be generated
	 *
	 * @internal param \other $array options in name-value pairs (e.g. 'scheme', 'lang')
	 * @access public
	 * @static
	 */
	public static function _rmt( $sContent, $sName = null, $sHttpEquiv = null, $options = array() )
	{
		self::$_clientScript->registerMetaTag( $sContent, $sName, $sHttpEquiv, $options );
	}

	/**
	 * Creates a relative URL based on the given controller and action information.
	 * @param $route
	 * @param array $options
	 * @param string $ampersand
	 *
	 * @internal param \the $string URL route. This should be in the format of 'ControllerID/ActionID'.
	 *
	 * @internal param \additional $array GET parameters (name=>value). Both the name and value will be URL-encoded.
	 *
	 * @internal param \the $string token separating name-value pairs in the URL.
	 * @return string the constructed URL
	 */
	public static function _cu( $route, $options = array(), $ampersand = '&' )
	{
		return self::$_application->createUrl( $route, $options, $ampersand );
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
		return self::$_request;
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

	/**
	 * @return null
	 */
	public static function _gu()
	{
		return self::$_user;
	}

	/**
	 * Returns the currently logged in user
	 * @return CWebUser
	 */
	public static function getCurrentUser()
	{
		return self::_gcu();
	}

	/**
	 * @return mixed
	 */
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

	/**

	 * @return #P#M#C\YiiXLBase._gu.isGuest|? #P#M#C\YiiXLBase._gu.isGuest|?
	 */
	public static function _ig()
	{
		return self::_gu()->isGuest;
	}

	/**
	 * Returns application parameters or default value if not found
	 * @see CModule::getParams
	 * @see CModule::setParams
	 * @param $paramName
	 * @param null $defaultValue
	 * @return mixed
	 */
	public static function getParam( $paramName, $defaultValue = null )
	{
		return self::_gp( $paramName, $defaultValue );
	}

	/**
	 * @param $paramName
	 * @param null $defaultValue
	 * @return #M#P#C\YiiXLBase._applicationParameters.itemAt|null|? #M#P#C\YiiXLBase._applicationParameters.itemAt|null|?
	 */
	public static function _gp( $paramName, $defaultValue = null )
	{
		if ( self::$_applicationParameters && self::$_applicationParameters->contains( $paramName ) )
		{
			return self::$_applicationParameters->itemAt( $paramName );
		}

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

	/**

	 * @return #M#P#C\YiiXLBase._application.getController|\CController|null|? #M#P#C\YiiXLBase._application.getController|null|?
	 */
	public static function _gc()
	{
		return ( null === self::$_controller ? self::$_controller = self::$_application->getController() :
			self::$_controller );
	}

	/**
	 * @param $id
	 * @param bool $createIfNull
	 * @return CComponent The component, if found
	 * @see CWebApplication::getComponent
	 */
	public static function getComponent( $id, $createIfNull = true )
	{
		return self::_gco( $id, $createIfNull );
	}

	/**
	 * @param $id
	 * @param bool $createIfNull
	 * @return #M#P#C\YiiXLBase._application.getComponent|? #M#P#C\YiiXLBase._application.getComponent|?
	 */
	public static function _gco( $id, $createIfNull = true )
	{
		return self::$_application->getComponent( $id, $createIfNull );
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
	 * @param $path
	 * @param bool $hashByName
	 * @param $level
	 *
	 * @internal param \the $string asset (file or directory) to be published
	 *
	 * @internal param \whether $boolean the published directory should be named as the hashed basename.
	 * If false, the name will be the hashed dirname of the path being published.
	 * Defaults to false. Set true if the path being published is shared among
	 * different extensions.
	 *
	 * @internal param \level $integer of recursive copying when the asset is a directory.
	 * Level -1 means publishing all subdirectories and files;
	 * Level 0 means publishing only the files DIRECTLY under the directory;
	 * level N means copying those directories that are within N levels.
	 * @return string an absolute URL to the published asset
	 * @throws CException if the asset to be published does not exist.
	 * @see CAssetManager::publish
	 */
	public static function _publish( $path, $hashByName = false, $level = -1 )
	{
		return self::$_application->getAssetManager()->publish( $path, $hashByName, $level );
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
		self::$_request->redirect( $url, $terminate, $statusCode );
	}

	/**
	 * Returns the value of a variable that is stored in the user session.
	 *
	 * This function is designed to be used by CWebUser descendant classes to
	 * store additional user information the user's session. A variable, if
	 * stored in the session using {@link _ss} can be retrieved back using this
	 * function.
	 *
	 * @param $stateName
	 * @param null $defaultValue
	 *
	 * @internal param \variable $string name
	 *
	 * @internal param \default $mixed value
	 * @return mixed the value of the variable. If it doesn't exist in the session,
	 * the provided default value will be returned
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
	 * @param $stateKeyParts
	 * @param null $defaultValue
	 *
	 * @internal param \Array $array of key parts
	 *
	 * @internal param \default $mixed value
	 * @return mixed the value of the variable. If it doesn't exist in the session,
	 * the provided default value will be returned
	 * @see _ss
	 * @see _gs
	 * @see CWebUser::setState
	 */
	public static function _ghs( $stateKeyParts, $defaultValue = null )
	{
		return self::_gs( xlHash::hash( implode( '.', $stateKeyParts ) ), $defaultValue );
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
	 * @param $stateName
	 * @param $stateValue
	 * @param null $defaultValue
	 * @return #M#C\null.setState|bool|? #M#C\null.setState|bool|?@internal param \variable $string name
	 *
	 * @internal param \variable $mixed value
	 *
	 * @internal param \default $mixed value. If $value === $defaultValue (i.e. null), the variable will be removed from the
	 * session
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
	 * @param $stateKeyParts
	 * @param $stateValue
	 * @param null $defaultValue
	 * @return #M#C\null.setState|bool|? #M#C\null.setState|bool|?@internal param array $array of key parts
	 *
	 * @internal param \variable $mixed value
	 *
	 * @internal param \default $mixed value
	 * @see _ss
	 * @see _gs
	 * @see CWebUser::setState
	 */
	public static function _shs( $stateKeyParts, $stateValue, $defaultValue = null )
	{
		return self::_ss( xlHash::hash( implode( '.', $stateKeyParts ) ), $stateValue, $defaultValue );
	}

	/**
	 * Stores a flash message.
	 * A flash message is available only in the current and the next requests.
	 * @param string $key key identifying the flash message
	 * @param mixed $value flash message
	 * @param mixed $defaultValue if this value is the same as the flash message, the flash message
	 * will be removed. (Therefore, you can use setFlash('key',null) to remove a flash message.)
	 * @see {@link xlHelperBase#_gf}
	 */
	public static function _sf( $key, $value, $defaultValue = null )
	{
		if ( null !== ( $_user = self::_gu() ) )
		{
			$_user->setFlash( $key, $value, $defaultValue );
		}
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
			{
				return false;
			}

			if ( null !== $max && $_value > $max )
			{
				return false;
			}

			if ( $nullIfZero && 0 == $_value )
			{
				return null;
			}
		}

		return $_value;
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
		return self::app()->getErrorHandler()->getError();
	}

	/**
	 * (PHP 5 &gt;= 5.1.0)<br/>
	 * Attach an SplObserver
	 * @link http://php.net/manual/en/splsubject.attach.php
	 * @param SplObserver $observer <p>
	 * The SplObserver to attach.
	 * </p>
	 * @return void
	 */
	public function attach( $observer )
	{
		// TODO: Implement attach() method.
	}

	/**
	 * (PHP 5 &gt;= 5.1.0)<br/>
	 * Detach an observer
	 * @link http://php.net/manual/en/splsubject.detach.php
	 * @param SplObserver $observer <p>
	 * The SplObserver to detach.
	 * </p>
	 * @return void
	 */
	public function detach( $observer )
	{
		// TODO: Implement detach() method.
	}

	/**
	 * (PHP 5 &gt;= 5.1.0)<br/>
	 * Notify an observer
	 * @link http://php.net/manual/en/splsubject.notify.php
	 * @return void
	 */
	public function notify( )
	{
		// TODO: Implement notify() method.
	}

	/**
	 * Gets the configuration options
	 * @param array $options
	 * @return xlIConfigurable
	 */
	public function setOptions( $options = array( ) )
	{
		// TODO: Implement setOptions() method.
	}

	/**
	 * @param \CWebApplication $application
	 */
	public static function setApplication( $application )
	{
		self::$_application = $application;
	}

	/**
	 * @return \CWebApplication
	 */
	public static function getApplication( )
	{
		return self::$_application;
	}

	/**
	 * @param \CAttributeCollection $applicationParameters
	 */
	public static function setApplicationParameters( $applicationParameters )
	{
		self::_swapValue( self::$_applicationParameters, $applicationParameters );
	}

	/**
	 * @return \CAttributeCollection
	 */
	public static function getApplicationParameters()
	{
		return self::$_applicationParameters;
	}
}
