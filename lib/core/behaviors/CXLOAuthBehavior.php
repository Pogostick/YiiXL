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
	* @var OAuth $_oauthObject Our OAuth object
	*/
	protected $_oauthObject = null;

	/**
	* @var array $_oauthToken The current token list
	*/
	protected $_oauthToken = null;

	//********************************************************************************
	//* Properties
	//********************************************************************************

	/**
	* @return array Retrieves the current OAuth token
	*/
	public function getOAuthToken() { return $this->_oauthToken; }

	/**
	* @return OAuth Retrieves the OAuth object
	*/
	public function getOAuthObject() { return $this->_oauthObject; }

	//********************************************************************************
	//* Constructor
	//********************************************************************************

	/***
	* Constructor
	*
	*/
	public function __construct()
	{
		//	No oauth? No run...
		if ( ! extension_loaded( 'oauth' ) )
			throw new CXLException( Yii::t( 'yiixl.core.behaviors', 'The "oauth" extension is not loaded. Please install and/or load the oath extension (PECL).' ) );

		//	Call daddy...
		parent::__construct();
	}

	//********************************************************************************
	//* Public Methods
	//********************************************************************************

	/**
	* Initialize this behavior
	*
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
		if ( ! $this->isAuthorized )
		{
			if ( isset( $_REQUEST[ 'oauth_token' ] ) )
			{
				if ( $this->_oauthObject->setToken( $_REQUEST[ 'oauth_token' ], $_REQUEST[ 'oauth_verifier' ] ) )
				{
					$_arToken = $this->_oauthObject->getAccessToken( $this->apiBaseUrl . $this->accessTokenUrl, null, $_REQUEST[ 'oauth_verifier' ] );
					$this->storeToken( $_arToken );
					$this->isAuthorized = true;
				}

				//	Raise our event
				if ( $this->isAuthorized )
					$this->onUserAuthorized( new CPSOAuthEvent( $this->_oauthToken ) );
			}
		}
	}

	/**                                         I
	* Appends the current token to the authorizeUrl option
	*
	* @param mixed $oToken
	*/
	public function getAuthorizeUrl()
	{
		$_arToken = $this->_oauthObject->getRequestToken( $this->apiBaseUrl . $this->requestTokenUrl, $this->callbackUrl );
		return $this->apiBaseUrl . $this->authorizeUrl . '?oauth_token=' . $_arToken[ 'oauth_token' ];
	}

	/**
	* Stores the current token in a member variable and in the user state oAuthToken
	*
	* @param array $oToken
	*/
	public function storeToken( $oToken = array() )
	{
		try
		{
			Yii::app()->user->setState( $this->getInternalName() . '_oAuthToken', $oToken );
			Yii::app()->user->setState( $this->getInternalName() . '_isAuthorized', $this->isAuthorized );
			$this->_oauthToken = $oToken;
		}
		catch ( Exception $_ex )
		{
			$_sName = $this->getInternalName();
			CPSLog::error( 'pogostick.behaviors', Yii::t( $_sName, 'Error storing OAuth token "{a}/{b}" : {c}', array( "{a}" => $oToken['oauth_token'], "{b}" => $oToken['oauth_token_secret'], "{c}" => $_ex->getMessage() ) ) );
		}
	}

	/**
	* Loads a token from the user state oAuthToken
	*
	*/
	public function loadToken()
	{
		$_oUser = Yii::app()->user;

		if ( $_oUser )
		{
			if ( null != ( $_oToken = $_oUser->getState( $this->getInternalName() . '_oAuthToken' ) ) )
			{
				$this->_oauthToken = $_oToken;
				$this->isAuthorized = ( $_oUser->getState( $this->getInternalName() . '_isAuthorized' ) == true );
			}
			else
				$_oToken = array();
		}
	}

	//********************************************************************************
	//* Private Methods
	//********************************************************************************

	/**
	* Our options
	*/
	private function getBaseOptions()
	{
		return(
			array(
				//	Required settings
				'callbackUrl' => 'string',
				'isAuthorized' => 'boolean:false',

				//	Urls
				'accessTokenUrl' => 'string:/oauth/access_token',
				'authorizeUrl' => 'string:/oauth/authorize',
				'requestTokenUrl' => 'string:/oauth/request_token',
			)
		);
	}

	//********************************************************************************
	//* Events
	//********************************************************************************

	/***
	 * User has been authorized event
	 * @param CPSOAuthEvent $oEvent
	 */
	public function onUserAuthorized( $oEvent )
	{
		$this->raiseEvent( 'onUserAuthorized', $oEvent );
	}

}