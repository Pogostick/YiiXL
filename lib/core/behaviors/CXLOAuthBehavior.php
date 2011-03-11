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
 * CXLOAuthBehavior
 * Provides OAuth support to any YiiXL component
 *
 * Introduces new event: onUserAuthorized raised when a user has been authorized
 *
 * @property-read OAuth $oauthObject The curent OAuth object
 * @property-read string $oauthToken The current token
 */
class CXLOAuthBehavior extends CBehavior implements IXLBehavior
{
	//********************************************************************************
	//* Constants
	//********************************************************************************

	/**
	 * Our logging tag
	 */
	const CLASS_LOG_TAG = 'yxl.core.behaviors.CXLOAuthBehavior';

	//********************************************************************************
	//* Properties
	//********************************************************************************

	/**
	 * The current token list
	 * @var array 
	 */
	protected $_oauthToken = null;

	/**
	 * Our OAuth object
	 * @var OAuth 
	 */
	protected $_oauthObject = null;
	
	/**
	 *
	 * @var string
	 */
	protected $_callbackUrl;
	/**
	 *
	 * @var boolean
	 */
	protected $_isAuthorized;
	/**
	 *
	 * @var string
	 */
	protected $_accessTokenUrl;
	/**
	 *
	 * @var string
	 */
	protected $_authorizeUrl;
	/**
	 *
	 * @var string
	 */
	protected $_requestTokenUrl;
	
	//********************************************************************************
	//* Property Accessors
	//********************************************************************************

	/**
	 * Retrieves the current OAuth token
	 * @return array 
	 */
	public function getOAuthToken()
	{
		return $this->_oauthToken;
	}

	/**
	 * Retrieves the OAuth object
	 * @return OAuth 
	 */
	public function getOAuthObject()
	{
		return $this->_oauthObject;
	}
	
	public function getCallbackUrl() 	
	{
		return $this->_callbackUrl;
	}

	public function setCallbackUrl( $callbackUrl )
	{
		$this->_callbackUrl = $callbackUrl;
		return $this;
	}

	public function getIsAuthorized()
	{
		return $this->_isAuthorized;
	}

	public function setIsAuthorized( $isAuthorized )
	{
		$this->_isAuthorized = $isAuthorized;
		return $this;
	}

	public function getAccessTokenUrl()
	{
		return $this->_accessTokenUrl;
	}

	public function setAccessTokenUrl( $accessTokenUrl )
	{
		$this->_accessTokenUrl = $accessTokenUrl;
		return $this;
	}

	/**                                         I
	 * Appends the current token to the authorizeUrl option
	 * @return string
	 */
	public function getAuthorizeUrl()
	{
		$_token = $this->_oauthObject->getRequestToken( $this->_apiBaseUrl . $this->_requestTokenUrl, $this->_callbackUrl );
		return $this->_apiBaseUrl . $this->_authorizeUrl . '?oauth_token=' . $_token['oauth_token'];
	}

	public function setAuthorizeUrl( $authorizeUrl )
	{
		$this->_authorizeUrl = $authorizeUrl;
		return $this;
	}

	public function getRequestTokenUrl()
	{
		return $this->_requestTokenUrl;
	}

	public function setRequestTokenUrl( $requestTokenUrl )
	{
		$this->_requestTokenUrl = $requestTokenUrl;
		return $this;
	}

	//********************************************************************************
	//* Constructor
	//********************************************************************************

	/**
	 * Constructor
	 */
	public function __construct()
	{
		//	No oauth? No run...
		if ( ! extension_loaded( 'oauth' ) )
		{
			throw new CXLException(
				Yii::t(
					self::CLASS_LOG_TAG, 
					'The "oauth" extension is not loaded. Please install and/or load the oath extension (PECL).'
				)
			);
		}

		//	Call daddy...
		parent::__construct();
	}

	//********************************************************************************
	//* Public Methods
	//********************************************************************************

	/**
	 * Initialize this behavior
	 */
	public function init()
	{
		//	Events...
		$this->attachEventHandler( 'onUserAuthorized', array( $this, 'userAuthorized' ) );

		//	Create our object...
		$this->_oauthObject = new OAuth( $this->apiKey, $this->apiSecretKey, OAUTH_SIG_METHOD_HMACSHA1, OAUTH_AUTH_TYPE_URI );

		//	Load any tokens we have...
		$this->loadToken();

		//	Have we been authenticated?
		if ( !$this->isAuthorized )
		{
			if ( isset( $_REQUEST['oauth_token'] ) )
			{
				if ( $this->_oauthObject->setToken( $_REQUEST['oauth_token'], $_REQUEST['oauth_verifier'] ) )
				{
					$_token = $this->_oauthObject->getAccessToken( 
						$this->_apiBaseUrl . $this->_accessTokenUrl,
						null, 
						$_REQUEST['oauth_verifier']
					);

					$this->storeToken( $_token );
					$this->_isAuthorized = true;
				}

				//	Raise our event
				if ( $this->_isAuthorized ) 
					$this->onUserAuthorized( new CPSOAuthEvent( $this->_oauthToken ) );
			}
		}
	}

	/**
	 * Stores the current token in a member variable and in the user state oAuthToken
	 * @param array $token
	 */
	public function storeToken( $token = array() )
	{
		try
		{
			XL::_ss( CXLHash::hash( 'OAuthToken' . $this->_apiBaseUrl ), $token );
			XL::_ss( CXLHash::hash( 'OAuthorized' . $this->_apiBaseUrl ), (boolean)$this->_isAuthorized );

			$this->_oauthToken = $token;
		}
		catch ( Exception $_ex )
		{
			XL::logError(
				self::CLASS_LOG_TAG,
				Yii::t( 
					self::CLASS_LOG_TAG, 
					'Error storing OAuth token "{a}/{b}" : {c}', 
					array( 
						"{a}" => $token['oauth_token'], 
						"{b}" => $token['oauth_token_secret'], 
						"{c}" => $_ex->getMessage() 
					) 
				) 
			);
		}
	}

	/**
	 * Loads a token from the user state oAuthToken
	 */
	public function loadToken()
	{
		$_token = array();
		
		if ( null != ( $_token = XL::_gs( CXLHash::hash( 'OAuthToken' . $this->_apiBaseUrl ) ) ) )
		{
			$this->_oauthToken = $_token;
			$this->_isAuthorized = ( true === XL::_gs( CXLHash::hash( 'OAuthorized' . $this->_apiBaseUrl ) ) );
		}
		
		return $_token;
	}

	//********************************************************************************
	//* Events
	//********************************************************************************

	/**
	 * User has been authorized event
	 * @param CXLOAuthEvent $event
	 */
	public function onUserAuthorized( $event )
	{
		$this->raiseEvent( 'onUserAuthorized', $event );
	}

}