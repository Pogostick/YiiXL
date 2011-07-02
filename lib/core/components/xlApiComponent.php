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
 * @package			yiixl.core.components
 * @category		Components
 * @since			v1.0.0
 *
 * @filesource
 */
/**
 * xlApiComponent provides a convenient base class for API access.
 *
 * Introduces three new events:
 *
 *  * onBeforeApiCall
 *  * onAfterApiCall
 *  * onRequestComplete
 *
 * Each are called respectively and pass the handler a xlApiEvent
 * object with details of the call.
 *
 * @event boolean onBeforeApiCall
 * @event boolean onAfterApiCall
 * @event boolean onRequestComplete
 */
class xlApiComponent extends xlApplicationComponent
{
	//********************************************************************************
	//* Constants
	//********************************************************************************

	/**
	 * @const string Our logging tag
	 */
	const
		CLASS_LOG_TAG = 'yiixl.core.components.xlApiComponent'
	;

	//********************************************************************************
	//* Properties
	//********************************************************************************

	/**
	 * @var string
	 */
	protected $_apiKey;
	/**
	 * @var string an alternate/second/secret API key
	 */
	protected $_altApiKey;
	/**
	 * @var string
	 */
	protected $_appendFormat;
	/**
	 * @var string
	 */
	protected $_apiBaseUrl;
	/**
	 * @var string
	 */
	protected $_apiQueryName;
	/**
	 * @var string
	 */
	protected $_apiToUse;
	/**
	 * @var string
	 */
	protected $_apiSubUrls;
	/**
	 * @var string
	 */
	protected $_httpMethod;
	/**
	 * @var integer
	 */
	protected $_returnFormat;
	/**
	 * @var string
	 */
	protected $_payload;
	/**
	 * @var string
	 */
	protected $_requestMap;
	/**
	 * @var string
	 */
	protected $_requireApiQueryName;
	/**
	 * @var string
	 */
	protected $_testApiKey;
	/**
	 * @var string
	 */
	protected $_testAltApiKey;
	/**
	 * @var string
	 */
	protected $_userAgent = 'YiiXL; (+http://github.com/Pogostick/yiixl/)';
	/**
	 * @var string
	 */
	protected $_lastErrorMessage;
	/**
	 * @var string
	 */
	protected $_lastErrorMessageExtra;
	/**
	 * @var string
	 */
	protected $_lastErrorCode;
	/**
	 * @var string The base url for this component
	 */
	protected $_baseUrl;
	/**
	 * @var boolean If true, options will be validated
	 */
	protected $_validateOptions = true;
	/**
	 * @var array $_validOptions The options considered valid
	 */
	protected $_validOptions = array();
	/**
	 * @var boolean If true, callbacks will be validated
	 */
	protected $_validateCallbacks = true;
	/**
	 * @var array $_validCallbacks The callbacks considered valid
	 */
	protected $_validCallbacks = array();
	/**
	 * @var array $_callbacks The list of client-side callbacks
	 */
	protected $_callbacks = array();
	/**
	 * @var string $_externalLibraryUrl The url of the external library (duh!)
	 */
	protected $_externalLibraryUrl = '/';

	//********************************************************************************
	//* Public Methods
	//********************************************************************************

	/**
	 * Initialize
	 */
	public function init()
	{
		//	Call daddy...
		parent::init();

		//	Attach our events...
		$this->attachEventHandler( 'onBeforeApiCall', array( $this, 'beforeApiCall' ) );
		$this->attachEventHandler( 'onAfterApiCall', array( $this, 'afterApiCall' ) );
		$this->attachEventHandler( 'onRequestComplete', array( $this, 'requestComplete' ) );
	}

	//********************************************************************************
	//* Public Methods
	//********************************************************************************

	/**
	 * Adds to the requestMap array
	 *
	 * @param string $label The "friendly" name for consumers
	 * @param string $parameterName The name of the API variable, if null, $label
	 * @param bool $required
	 * @param array $options
	 * @param string $apiName
	 * @param string $subApiName
	 * @return bool True if operation succeeded
	 * @see makeMapItem
	 * @see makeMapArray
	 */
	public function addRequestMapping( $label, $parameterName = null, $required = false, $options = array(), $apiName = null, $subApiName = null )
	{
		//	Save for next call
		static $_lastApiName;
		static $_lastAction;

		//	Set up statics so next call can omit those parameters.
		if ( null !== $apiName && $apiName != $_lastApiName )
		{
			$_lastApiName = $apiName;
		}

		//	Make sure sub API name is set...
		if ( null === $_lastAction && null == $subApiName )
		{
			$subApiName = '/';
		}

		if ( null !== $subApiName && $subApiName != $_lastAction )
		{
			$_lastAction = $subApiName;
		}

		//	Build the options
		$_mapOptions = array(
			'name' => ( null !== $parameterName ) ? $parameterName : $label,
			'required' => $required
		);

		//	Add on any supplied options
		if ( !empty( $options ) )
		{
			$_mapOptions = array_merge( $_mapOptions, $options );
		}

		//	Add the mapping...
		if ( null == $_lastApiName && null == $_lastAction )
		{
			return false;
		}

		//	Add mapping...
		if ( null === $this->_requestMap )
		{
			$this->_requestMap = array();
		}

		$this->_requestMap[$_lastApiName][$_lastAction][$label] = $_mapOptions;

		return true;
	}

	/**
	 * Makes the actual HTTP request based on settings
	 * @param string $subType
	 * @param array $payload
	 * @param string $requestMethod
	 * @return string
	 */
	protected function makeRequest( $subType = '/', $payload = null, $requestMethod = 'GET' )
	{
		//	Make sure apiQueryName is set...
		if ( $this->_requireApiQueryName && null === $this->_apiQueryName )
		{
			throw new xlApiException(
				Yii::t(
					__CLASS__, 'Required option "apiQueryName" is not set.'
				)
			);
		}

		//	Default...
		$_payload = $this->_payload;

		//	Check data...
		if ( is_array( $payload ) && !empty( $payload ) )
		{
			$_payload = array_merge( $_payload, $payload );
		}

		//	Check subtype...
		if ( !empty( $subType ) && is_array( $this->_requestMap[$this->_apiToUse] ) )
		{
			if ( !array_key_exists( $subType, $this->_requestMap[$this->_apiToUse] ) )
			{
				throw new xlApiException(
					Yii::t(
						__CLASS__,
						'Invalid API SubType specified for "{apiToUse}". Valid subtypes are "{subTypes}"',
						array(
							'{apiToUse}' => $this->_apiToUse,
							'{subTypes}' => implode(
								', ',
								array_keys(
									$this->_requestMap[$this->_apiToUse]
								)
							)
						)
					)
				);
			}
		}

		//	Build the url...
		$_url = rtrim( $this->_apiBaseUrl, '/' ) . '/';

		if ( isset( $this->_apiSubUrls[$this->_apiToUse] ) && '/' != $this->_apiSubUrls[$this->_apiToUse] )
		{
			$_url .= $this->_apiSubUrls[$this->_apiToUse];
		}

		//	Add the API key...
		if ( $this->_requireApiQueryName )
		{
			$_queryString = $this->_apiQueryName . '=' . $this->_apiKey;
		}

		//	Add the request data to the Url...
		if ( is_array( $this->_requestMap ) && !empty( $subType ) && isset( $this->_requestMap[$this->_apiToUse] ) )
		{
			$_requestMap = $this->_requestMap[$this->_apiToUse][$subType];
			$_result = array();

			//	Add any extra payload parameters unchecked to the query string...
			foreach ( $_payload as $_key => $_value )
			{
				if ( !array_key_exists( $_key, $_requestMap ) )
				{
					$_queryString .= '&' . $_key . '=' . urlencode( $_value );
					unset( $_payload[$_key] );
				}
			}

			//	Now build the url...
			foreach ( $_requestMap as $_key => $_mapping )
			{
				//	Tag as done...
				$_result[] = $_key;

				//	Is there a default value?
				if ( isset( $_mapping['default'] ) && !isset( $_payload[$_key] ) )
				{
					$_payload[$_key] = $_mapping['default'];
				}

				if ( isset( $_mapping['required'] ) && $_mapping['required'] && !array_key_exists( $_key, $_payload ) )
				{
					throw new xlApiException(
						Yii::t(
							__CLASS__, 'Required parameter {param} was not included in payload', array(
																										  '{param}' => $_key,
																									 )
						)
					);
				}

				//	Add to query string
				if ( isset( $_payload[$_key] ) )
				{
					$_queryString .= '&' . $_mapping['name'] . '=' . urlencode( $_payload[$_key] );
				}
			}
		}
			//	Handle non-requestMap call...
		else {
			if ( is_array( $_payload ) )
			{
				foreach ( $_payload as $_key => $_value )
				{
					if ( isset( $_payload[$_key] ) )
					{
						$_queryString .= '&' . $_key . '=' . urlencode( $_value );
					}
				}
			}
		}

		if ( $this->_debugLevel > 3 )
		{
			XL::logDebug( 'Calling onBeforeApiCall', self::CLASS_LOG_TAG );
		}

		//	Handle events...
		$_event = new xlApiEvent( $_url, $_queryString, null, $this );
		$this->onBeforeApiCall( $_event );

		if ( $this->_debugLevel > 3 )
		{
			XL::logDebug( 'Making request: ' . $_queryString, self::CLASS_LOG_TAG );
		}

		//	Ok, we've build our request, now let's get the results...
		$_response = XL::makeHttpRequest( $_url, $_queryString, $requestMethod, $this->_userAgent );

		if ( $this->_debugLevel > 3 )
		{
			XL::logDebug( 'Call complete', self::CLASS_LOG_TAG );
		}

		if ( $this->_debugLevel > 4 )
		{
			XL::logDebug( 'Response: ' . var_export( $_response, true ), self::CLASS_LOG_TAG );
		}

		//	Handle events...
		$_event->setResults( $_response );
		$this->onAfterApiCall( $_event );

		//	If user doesn't want JSON output, then reformat
		switch ( $this->_returnFormat )
		{
			case XL::OF_XML:
				$_response = xlTransform::arrayToXml( json_decode( $_response, true ), 'Results' );
				break;

			case XL::OF_ASSOC_ARRAY:
				$_response = json_decode( $_response, true );
				break;

			default: //	Naked
				break;
		}

		//	Raise our completion event...
		$_event->setResults( $_response );
		$this->onRequestComplete( $_event );

		//	Return results...
		return $_response;
	}

	//********************************************************************************
	//* Events and Handlers
	//********************************************************************************

	/**
	 * Raises the onBeforeApiCall event
	 *
	 * @param xlApiEvent $event
	 */
	public function onBeforeApiCall( xlApiEvent $event )
	{
		$this->raiseEvent( 'onBeforeApiCall', $event );
	}

	/**
	 * Base event handler
	 * @param xlApiEvent $event
	 * @return boolean
	 */
	public function beforeApiCall( xlApiEvent $event )
	{
		return true;
	}

	/**
	 * Raises the onAfterApiCall event. $event contains "raw" return data
	 *
	 * @param xlApiEvent $event
	 */
	public function onAfterApiCall( xlApiEvent $event )
	{
		$this->raiseEvent( 'onAfterApiCall', $event );
	}

	/**
	 * Base event handler
	 * @param xlApiEvent $event
	 * @return boolean
	 */
	public function afterApiCall( xlApiEvent $event )
	{
		return true;
	}

	/**
	 * Raises the onRequestComplete event. $event contains "processed" return data (if applicable)
	 *
	 * @param xlApiEvent $event
	 */
	public function onRequestComplete( xlApiEvent $event )
	{
		$this->raiseEvent( 'onRequestComplete', $event );
	}

	/**
	 * Base event handler
	 * @param xlApiEvent $event
	 * @return boolean
	 */
	public function requestComplete( xlApiEvent $event )
	{
		return true;
	}

	//********************************************************************************
	//* Property Accessors
	//********************************************************************************

	/**
	 * @return string
	 */
	public function getAltApiKey()
	{
		return $this->_altApiKey;
	}

	/**
	 * @param $altApiKey
	 */
	public function setAltApiKey( $altApiKey )
	{
		$this->_altApiKey = $altApiKey;
	}

	/**
	 * @return string
	 */
	public function getAppendFormat()
	{
		return $this->_appendFormat;
	}

	/**
	 * @param $appendFormat
	 */
	public function setAppendFormat( $appendFormat )
	{
		$this->_appendFormat = $appendFormat;
	}

	/**
	 * @return string
	 */
	public function getApiBaseUrl()
	{
		return $this->_apiBaseUrl;
	}

	/**
	 * @param $apiBaseUrl
	 */
	public function setApiBaseUrl( $apiBaseUrl )
	{
		$this->_apiBaseUrl = $apiBaseUrl;
	}

	/**
	 * @return string
	 */
	public function getApiKey()
	{
		return $this->_apiKey;
	}

	/**
	 * @param $apiKey
	 */
	public function setApiKey( $apiKey )
	{
		$this->_apiKey = $apiKey;
	}

	/**
	 * @return string
	 */
	public function getApiQueryName()
	{
		return $this->_apiQueryName;
	}

	/**
	 * @param $apiQueryName
	 */
	public function setApiQueryName( $apiQueryName )
	{
		$this->_apiQueryName = $apiQueryName;
	}

	/**
	 * @return string
	 */
	public function getApiToUse()
	{
		return $this->_apiToUse;
	}

	/**
	 * @param $apiToUse
	 */
	public function setApiToUse( $apiToUse )
	{
		$this->_apiToUse = $apiToUse;
	}

	/**
	 * @return string
	 */
	public function getApiSubUrls()
	{
		return $this->_apiSubUrls;
	}

	/**
	 * @param $apiSubUrls
	 */
	public function setApiSubUrls( $apiSubUrls )
	{
		$this->_apiSubUrls = $apiSubUrls;
	}

	/**
	 * @return string
	 */
	public function getHttpMethod()
	{
		return $this->_httpMethod;
	}

	/**
	 * @param $httpMethod
	 */
	public function setHttpMethod( $httpMethod )
	{
		$this->_httpMethod = $httpMethod;
	}

	/**
	 * @return int
	 */
	public function getReturnFormat()
	{
		return $this->_returnFormat;
	}

	/**
	 * @param $returnFormat
	 */
	public function setReturnFormat( $returnFormat )
	{
		$this->_returnFormat = $returnFormat;
	}

	/**
	 * @return array|string
	 */
	public function getPayload()
	{
		return $this->_payload;
	}

	/**
	 * @param $payload
	 * @return \xlApiComponent
	 */
	public function setPayload( $payload )
	{
		$this->_payload = $payload;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getRequestMap()
	{
		return $this->_requestMap;
	}

	/**
	 * @param $requestMap
	 * @return \xlApiComponent
	 */
	public function setRequestMap( $requestMap )
	{
		$this->_requestMap = $requestMap;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getRequireApiQueryName()
	{
		return $this->_requireApiQueryName;
	}

	/**
	 * @param $requireApiQueryName
	 * @return \xlApiComponent
	 */
	public function setRequireApiQueryName( $requireApiQueryName )
	{
		$this->_requireApiQueryName = $requireApiQueryName;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getTestApiKey()
	{
		return $this->_testApiKey;
	}

	/**
	 * @param $testApiKey
	 * @return \xlApiComponent
	 */
	public function setTestApiKey( $testApiKey )
	{
		$this->_testApiKey = $testApiKey;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getTestAltApiKey()
	{
		return $this->_testAltApiKey;
	}

	/**
	 * @param $testAltApiKey
	 * @return \xlApiComponent
	 */
	public function setTestAltApiKey( $testAltApiKey )
	{
		$this->_testAltApiKey = $testAltApiKey;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getUserAgent()
	{
		return $this->_userAgent;
	}

	/**
	 * @param $userAgent
	 * @return \xlApiComponent
	 */
	public function setUserAgent( $userAgent )
	{
		$this->_userAgent = $userAgent;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getLastErrorMessage()
	{
		return $this->_lastErrorMessage;
	}

	/**
	 * @param $lastErrorMessage
	 * @return \xlApiComponent
	 */
	public function setLastErrorMessage( $lastErrorMessage )
	{
		$this->_lastErrorMessage = $lastErrorMessage;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getLastErrorMessageExtra()
	{
		return $this->_lastErrorMessageExtra;
	}

	/**
	 * @param $lastErrorMessageExtra
	 * @return \xlApiComponent
	 */
	public function setLastErrorMessageExtra( $lastErrorMessageExtra )
	{
		$this->_lastErrorMessageExtra = $lastErrorMessageExtra;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getLastErrorCode()
	{
		return $this->_lastErrorCode;
	}

	/**
	 * @param $lastErrorCode
	 * @return \xlApiComponent
	 */
	public function setLastErrorCode( $lastErrorCode )
	{
		$this->_lastErrorCode = $lastErrorCode;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getBaseUrl()
	{
		return $this->_baseUrl;
	}

	/**
	 * @param $baseUrl
	 * @return \xlApiComponent
	 */
	public function setBaseUrl( $baseUrl )
	{
		$this->_baseUrl = $baseUrl;
		return $this;
	}

	/**
	 * @return bool
	 */
	public function getValidateOptions()
	{
		return $this->_validateOptions;
	}

	/**
	 * @param $validateOptions
	 * @return \xlApiComponent
	 */
	public function setValidateOptions( $validateOptions )
	{
		$this->_validateOptions = $validateOptions;
		return $this;
	}

	/**
	 * @return array
	 */
	public function getValidOptions()
	{
		return $this->_validOptions;
	}

	/**
	 * @param $validOptions
	 * @return \xlApiComponent
	 */
	public function setValidOptions( $validOptions )
	{
		$this->_validOptions = $validOptions;
		return $this;
	}

	/**
	 * @return bool
	 */
	public function getValidateCallbacks()
	{
		return $this->_validateCallbacks;
	}

	/**
	 * @param $validateCallbacks
	 * @return \xlApiComponent
	 */
	public function setValidateCallbacks( $validateCallbacks )
	{
		$this->_validateCallbacks = $validateCallbacks;
		return $this;
	}

	/**
	 * @return array
	 */
	public function getValidCallbacks()
	{
		return $this->_validCallbacks;
	}

	/**
	 * @param $validCallbacks
	 * @return \xlApiComponent
	 */
	public function setValidCallbacks( $validCallbacks )
	{
		$this->_validCallbacks = $validCallbacks;
		return $this;
	}

	/**
	 * @return array
	 */
	public function getCallbacks()
	{
		return $this->_callbacks;
	}

	/**
	 * @param $callbacks
	 * @return \xlApiComponent
	 */
	public function setCallbacks( $callbacks )
	{
		$this->_callbacks = $callbacks;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getExternalLibraryUrl()
	{
		return $this->_externalLibraryUrl;
	}

	/**
	 * @param $externalLibraryUrl
	 * @return \xlApiComponent
	 */
	public function setExternalLibraryUrl( $externalLibraryUrl )
	{
		$this->_externalLibraryUrl = $externalLibraryUrl;
		return $this;
	}
}