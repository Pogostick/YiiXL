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
 * xlOAuthBehavior
 *
 * @property array $events
 * @property boolean $enabled
 * @property xlComponent $owner
 */
/**
 * xlOAuthBehavior
 * Provides OAuth support to any YiiXL component
 *
 * @event userAuthorized raised when a user has been authorized
 *
 * @property-read OAuth $oauthObject The current OAuth object
 * @property-read string $oauthToken The current token
 * @property-read xlApiComponent $owner
 */
class xlOAuthBehavior extends xlBehavior
{
	//********************************************************************************
	//* Constants
	//********************************************************************************

	/**
	 * @const string Our logging tag
	 */
	const
		CLASS_LOG_TAG = 'yxl.core.behaviors.xlOAuthBehavior'
	;

	//********************************************************************************
	//* Properties
	//********************************************************************************

	/**
	 * @var array The current token list
	 */
	protected $_oauthToken = null;
	/**
	 * @var OAuth Our OAuth object
	 */
	protected $_oauthObject = null;
	/**
	 * @var string
	 */
	protected $_callbackUrl;
	/**
	 * @var boolean
	 */
	protected $_isAuthorized;
	/**
	 * @var string
	 */
	protected $_accessTokenUrl;
	/**
	 * @var string
	 */
	protected $_authorizeUrl;
	/**
	 * @var string
	 */
	protected $_requestTokenUrl;

	//********************************************************************************
	//* Constructor
	//********************************************************************************

	/**
	 * Constructor
	 */
	public function __construct()
	{
		//	No oauth? No run...
		if ( !extension_loaded( 'oauth' ) )
		{
			throw new xlException(
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
		$this->_oauthObject = new OAuth(
			$this->_owner->getApiKey(),
			$this->_owner->getAltApiKey(),
			OAUTH_SIG_METHOD_HMACSHA1,
			OAUTH_AUTH_TYPE_URI
		);

		//	Load any tokens we have...
		$this->loadToken();

		//	Have we been authenticated?
		if ( !$this->_isAuthorized )
		{
			if ( isset( $_REQUEST, $_REQUEST['oauth_token'], $_REQUEST['oauth_verifier'] ) )
			{
				if ( $this->_oauthObject->setToken( $_REQUEST['oauth_token'], $_REQUEST['oauth_verifier'] ) )
				{
					$_token = $this->_oauthObject->getAccessToken(
						$this->_owner->getApiBaseUrl() . $this->_accessTokenUrl,
						null,
						$_REQUEST['oauth_verifier']
					);

					$this->storeToken( $_token );
					$this->_isAuthorized = true;
				}

				//	Raise our event
				if ( $this->_isAuthorized )
				{
					$this->onUserAuthorized( new xlOAuthEvent( $this->_oauthToken ) );
				}
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
			XL::_ss( xlHash::hash( 'OAuthToken' . $this->_owner->getApiBaseUrl() ), $token );
			XL::_ss( xlHash::hash( 'OAuthorized' .$this->_owner->getApiBaseUrl() ), ( boolean )$this->_isAuthorized );

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
	 * @return string|null
	 */
	public function loadToken()
	{
		if ( null != ( $_token = XL::_gs( xlHash::hash( 'OAuthToken' . $this->_owner->getApiBaseUrl() ) ) ) )
		{
			$this->_oauthToken = $_token;
			$this->_isAuthorized = ( true === XL::_gs( xlHash::hash( 'OAuthorized' . $this->_owner->getApiBaseUrl() ) ) );
		}

		return $_token;
	}

	//********************************************************************************
	//* Events
	//********************************************************************************

	/**
	 * User has been authorized event
	 * @param xlOAuthEvent $event
	 */
	public function onUserAuthorized( $event )
	{
		$this->raiseEvent( 'onUserAuthorized', $event );
	}

	//********************************************************************************
	//* Property Accessors
	//********************************************************************************

	/**
	 * @return string
	 */
	public function getCallbackUrl()
	{
		return $this->_callbackUrl;
	}

	/**
	 * @param $callbackUrl
	 * @return \xlOAuthBehavior
	 */
	public function setCallbackUrl( $callbackUrl )
	{
		$this->_callbackUrl = $callbackUrl;
		return $this;
	}

	/**
	 * @return bool
	 */
	public function getIsAuthorized()
	{
		return $this->_isAuthorized;
	}

	/**
	 * @param $isAuthorized
	 * @return \xlOAuthBehavior
	 */
	public function setIsAuthorized( $isAuthorized )
	{
		$this->_isAuthorized = $isAuthorized;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getAccessTokenUrl()
	{
		return $this->_accessTokenUrl;
	}

	/**
	 * @param $accessTokenUrl
	 * @return \xlOAuthBehavior
	 */
	public function setAccessTokenUrl( $accessTokenUrl )
	{
		$this->_accessTokenUrl = $accessTokenUrl;
		return $this;
	}

	/**										 I
	 * Appends the current token to the authorizeUrl option
	 * @return string
	 */
	public function getAuthorizeUrl()
	{
		$_token = $this->_oauthObject->getRequestToken( $this->_owner->getApiBaseUrl() . $this->_requestTokenUrl, $this->_callbackUrl );
		return $this->_owner->getApiBaseUrl() . $this->_authorizeUrl . '?oauth_token=' . $_token['oauth_token'];
	}

	/**
	 * @param $authorizeUrl
	 * @return \xlOAuthBehavior
	 */
	public function setAuthorizeUrl( $authorizeUrl )
	{
		$this->_authorizeUrl = $authorizeUrl;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getRequestTokenUrl()
	{
		return $this->_requestTokenUrl;
	}

	/**
	 * @param $requestTokenUrl
	 * @return \xlOAuthBehavior
	 */
	public function setRequestTokenUrl( $requestTokenUrl )
	{
		$this->_requestTokenUrl = $requestTokenUrl;
		return $this;
	}

	/**
	 * Retrieves the OAuth object
	 * @return OAuth
	 */
	public function getOAuthObject()
	{
		return $this->_oauthObject;
	}

	/**
	 * @param \OAuth $oauthObject
	 * @return \xlOAuthBehavior $this
	 */
	protected function _setOauthObject( $oauthObject )
	{
		$this->_oauthObject = $oauthObject;
		return $this;
	}

	/**
	 * Retrieves the current OAuth token
	 * @return array
	 */
	public function getOAuthToken()
	{
		return $this->_oauthToken;
	}

	/**
	 * @param array $oauthToken
	 * @return \xlOAuthBehavior $this
	 */
	protected function _setOauthToken( $oauthToken )
	{
		$this->_oauthToken = $oauthToken;
		return $this;
	}
}