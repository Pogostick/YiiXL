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
 * @subpackage core.components
 *
 * @filesource
 */
/**
 * xlRestConsumerComponent
 *
 * Introduces three new events:
 *
 * onBeforeRestCall
 * onAfterRestCall
 * onRequestComplete
 */
class xlRestConsumerComponent extends xlApplicationComponent
{
	//********************************************************************************
	//* Constants
	//********************************************************************************

	/**
	 * Our logging tag
	 */
	const	CLASS_LOG_TAG = 'yiixl.core.components.xlRestConsumerComponent';

	//********************************************************************************
	//* Properties
	//********************************************************************************

	/**
	 * @var string $_apiKey Your API key
	 */
	protected $_apiKey;
	/**
	 * @var string an alternate/second API key
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
	protected $_apiToUseprotected;
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
	protected $_userAgent = 'YiiXL; (+http://github.com/Pogostick/YiiXL)';
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
	* Preinitialize
	*/
	public function init()
	{
		//	Call daddy
		parent::init();

		//	Attach our events
		$this->attachEventHandler( 'onBeforeApiCall', array( $this, 'beforeApiCall' ) );
		$this->attachEventHandler( 'onAfterApiCall', array( $this, 'afterApiCall' ) );
		$this->attachEventHandler( 'onRequestComplete', array( $this, 'requestComplete' ) );
	}

	//********************************************************************************
	//* Private Methods
	//********************************************************************************

	/**
	* Creates an array for requestMap
	*
	* @param array $apiMap The map of items to insert into the array. Format is the same as {@link makeMapItem}
	* @param bool $setRequestMap If false, will NOT insert constructed array into {@link requestMap}
	* @return array Returns the constructed array item ready to insert into your requestMap
	* @see makeMapItem
	*/
	protected function makeMapArray( $apiName, $subApiName = null, array $apiMap, $setRequestMap = true )
	{
		$_parameters = array();

		foreach ( $apiMap as $_key => $_value )
		{
			$_mapItem = $this->makeMapItem(
				( in_array( 'label', $_value ) ) ? $_value[ 'name' ] : $_value[ 'label' ],
				$_value[ 'name' ],
				( in_array( 'required', $_value ) ) ? $_value[ 'required' ] : false,
				( in_array( 'options', $_value ) ) ? $_value[ 'options' ] : null
			);

			$_mapItem = ( $subApiName != null ) ? array( $subApiName => $_mapItem ) : array( $_key, $_mapItem );

			if ( $setRequestMap )
				$this->requestMap[ $apiName ] = $_mapItem;

			array_merge( $_parameters[ $apiName ], $_mapItem );
		}

		return( $_parameters );
	}

	/**                                                   S
	* Creates an entry for requestMap and inserts it into the array.
	*
	* @param string $itemLabel The label or friendly name of this map item
	* @param string $parameterName The actual parameter name to send to API. If not specified, will default to $itemLabel
	* @param bool $required Set to true if the parameter is required
	* @param array $options If supplied, will merge with generated options
	* @param array $target If supplied, will insert into array
	* @return array Returns the constructed array item ready to insert into your requestMap
	*/
	protected function makeMapItem( $itemLabel, $parameterName = null, $required = false, $options = array(), array &$target = null )
	{
		//	Build default settings
		$_mappingOptions = array(
			'name' => ( null != $parameterName ) ? $parameterName : $itemLabel,
			'required' => $required
		);

		//	Add on supplied options
		if ( ! empty( $options ) )
			$_mappingOptions = array_merge( $_mappingOptions, $options );

		//	Insert for caller if requested
		if ( null != $target )
			$target[ $itemLabel ] = $_mappingOptions;

		//	Return our array
		return( $_mappingOptions );
	}

	//********************************************************************************
	//* Event Handlers
	//********************************************************************************

	/**
	* Raises the onBeforeApiCall event
	* @param xlApiEvent $event
	*/
	public function onBeforeApiCall( $event )
	{
		$this->raiseEvent( 'onBeforeApiCall', $event );
	}

	/**
	 * Base event handler
	 * @param xlApiEvent $event
	 */
	public function beforeApiCall( $event )
	{
	}

	/**
	* Raises the onAfterApiCall event. $event contains "raw" return data
	* @param xlApiEvent $event
	*/
	public function onAfterApiCall( $event )
	{
		$this->raiseEvent( 'onAfterApiCall', $event );
	}

	/**
	 * Base event handler
	 * @param xlApiEvent $event
	 */
	public function afterApiCall( $event )
	{
	}

	/**
	* Raises the onRequestComplete event. $event contains "processed" return data (if applicable)
	* @param xlApiEvent $event
	*/
	public function onRequestComplete( $event )
	{
		$this->raiseEvent( 'onRequestComplete', $event );
	}

	/**
	 * Base event handler
	 * @param xlApiEvent $event
	 */
	public function requestComplete( $event )
	{
	}

}