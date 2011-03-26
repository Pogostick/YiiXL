<?php
/**
 * This file is part of YiiXL
 * Copyright (c) 2009-2011, Pogostick, LLC. All rights reserved.
 *
 * @link http://www.pogostick.com Pogostick, LLC.
 * @license http://www.pogostick.com/licensing
 * @author Jerry Ablan <jablan@pogostick.com>
 *
 * @since v1.0.0
 *
 * @package yiixl
 * @subpackage core.ui
 *
 * @filesource
 */
/**
 * CXLWidget
 * Pretty much identical to CXLComponent but offers a little bit of extra functionality.
 * Forced to not be a descendent of {@see CIXComponent} because of the depth of the
 * {@see CInputWidget} heirarchy. Thus this object duplicates {@see CXLComponent}.
 */
class CXLWidget extends CInputWidget implements IXLComponent
{
	//********************************************************************************
	//* Constants
	//********************************************************************************

	/**
	 * Our logging tag
	 */
	const
		CLASS_LOG_TAG = 'yiixl.core.ui.CXLWidget';

	//********************************************************************************
	//* Member Variables
	//********************************************************************************

	/**
	 * @var integer $_debugLevel User-defined debug flag. false = OFF, anything else is up to you.
	 */
	protected $_debugLevel = false;
	/**
	 * @var array $_options Our configuration options
	 */
	protected $_options;
	/**
	 * @var array $_behaviorMethods Imported attached behavior methods
	 */
	protected $_behaviorMethods;
	/**
	 * @var array $_cssQueue Our CSS files
	 */
	protected $_cssQueue = array( );
	/**
	 * @var array $_scriptQueue Our JS files
	 */
	protected $_scriptQueue = array( );

	//********************************************************************************
	//* Property Accessors
	//********************************************************************************

	/**
	 * Gets the debug level
	 * @return integer
	 */
	public function getDebugLevel()
	{
		return $this->_debugLevel;
	}

	/**
	 * Sets the debug level
	 * @param integer The new debug level
	 */
	public function setDebugLevel( $value = false )
	{
		$this->_debugLevel = $value;
		return $this;
	}

	/**
	 * Gets configuration options
	 * @return array
	 */
	public function getOptions()
	{
		return $this->_options;
	}

	/**
	 * Gets the list of registered behavior methods
	 * @return array
	 */
	public function getBehaviorMethods()
	{
		return $this->_behaviorMethods;
	}

	/**
	 * Loads the configuration options
	 * @param array
	 * @return CXLComponent
	 */
	public function loadOptions( $options = array() )
	{
		$this->_options = $options;
		return $this;
	}
	
	/**
	 * Returns the css file queue
	 * @return array
	 */
	public function getCssQueue()
	{
		return $this->_cssQueue;
	}

	/**
	 * Sets the css file queue
	 * @return array
	 */
	public function setCssQueue( $value )
	{
		$this->_cssQueue = $value;
		return $this;
	}

	/**
	 * Gets a single configuration option
	 * @return mixed
	 */
	public function getOption( $key, $defaultValue = null, $unsetAfter = false )
	{
		return YiiXL::o( $this->_options, $key, $defaultValue, $unsetAfter );
	}

	//********************************************************************************
	//* Public Methods
	//********************************************************************************

	/**
	 * Constructor
	 * @param CBaseController $owner The owner/creator of this widget. Either a widget or a controller.
	 * @param array $options Additional options to pass on to the widget
	 */
	public function __construct( $owner = null, $options = array() )
	{
		parent::__construct( $owner );

		//	Load our configuration
		$this->_loadConfiguration( $options );
	}

	/**
	 * Initialize this widget
	 */
	public function init()
	{
		parent::init();

		//	Push any optional script files
		foreach ( YiiXL::o( $options, '_scriptFiles', array( ), true ) as $_script )
			$this->pushScriptFile( $_script );

		//	And CSS
		foreach ( YiiXL::o( $options, '_cssFiles', array( ), true ) as $_css )
			$this->pushCssFile( $_css );
	}

	/**
	 * Executes the widget.
	 * This method is called by {@link CBaseController::endWidget}.
	 */
	public function run()
	{
		//	Phone home
		parent::run();

		//	Render!
		$this->_render();
	}

	//********************************************************************************
	//* Extension Methods
	//********************************************************************************

	/**
	 * Pushes a css file onto the page load stack.
	 * @param string $path Path of css relative to doc_root
	 * @param string $id
	 */
	public function pushCssFile( $path, $media = 'screen' )
	{
		array_push(
			$this->_cssQueue, array(
			'path' => $path,
			'media' => $media,
			)
		);
	}

	/**
	 * Pops a css file off the top of the page load stack.
	 * @return string|null
	 */
	public function popCssFile()
	{
		return array_shift( $this->_cssQueue );
	}

	/**
	 * Pushes a script onto the page load stack.
	 *
	 * @param string $path Path of script relative to doc_root
	 * @param integer $position
	 * @param string $id
	 */
	public function pushScriptFile( $path, $position = CClientScript::POS_HEAD )
	{
		array_push(
			$this->_scriptQueue, array(
			'path' => $path,
			'position' => $position,
			)
		);

		$this->_scriptQueue[] = array( $path, $position );
	}

	/**
	 * Pops a script file off the top of the page load stack.
	 * @return string|null
	 */
	public function popScriptFile()
	{
		return array_shift( $this->_scriptQueue );
	}

	//********************************************************************************
	//* Private Methods
	//********************************************************************************

	/**
	 * Renders the widget queues in the right order/sequence
	 */
	protected function _render()
	{
		$_endTag = $this->_renderContainer();

		$this->_renderCss();
		$this->_renderHtml();
		$this->_renderScripts();

		$this->_renderContainer( $_endTag );
	}

	/**
	 * Renders either an open or close container tag for this widget
	 * @param string $endTag
	 * @return string The name of the tag used to open the container
	 */
	protected function _renderContainer( $endTag = null )
	{
		if ( null === $endTag )
		{
			echo YiiXL::closeTag( $endTag );
			return false;
		}

		//	Get our tag name and make it so...
		echo YiiXL::tag(
			$_tag = $this->getOption( 'tagName', 'div' ),
			$this->getOption( 'containerOptions', array() ),
			null,
			false
		);

		//	Return tag so we can close it...
		return $_tag;
	}

	/**
	 * Renders any CSS code for the widget. We don't technically render here
	 * we merely post up to the master Yii arbitrator
	 * @return boolean
	 */
	protected function _renderCss()
	{
		$_baseUrl = YiiXL::_gbu();

		//	Load css files and unset from array...
		while ( null !== ( $_file = array_shift( $this->_scriptQueue ) ) )
			YiiXL::_rcf( $_baseUrl . $_file['path'] , $_file['media'] );

		return true;
	}

	/**
	 * Renders any HTML for the widget
	 * @return boolean
	 */
	protected function _renderHtml()
	{
		//	nada
		return true;
	}

	/**
	 * Renders any CSS code for the widget. We don't technically render here
	 * we merely post up to the master Yii arbitrator
	 * @return boolean
	 */
	protected function _renderScripts()
	{
		$_baseUrl = YiiXL::_gbu();

		//	Load css files and unset from array...
		while ( null !== ( $_file = array_shift( $this->_scriptQueue ) ) )
			YiiXL::_rsf( $_baseUrl . $_file['path'] , $_file['position'] );

		return true;
	}

	/**
	 * Loads an array into properties if they exist.
	 * @param array $options
	 */
	protected function _loadConfiguration( $options = array( ), $overwriteExisting = true )
	{
		//	Make a copy for posterity
		if ( $overwriteExisting || empty( $this->_options ) ) $this->_options = $options;
		else $this->_options = array_merge( $this->_options, $options );

		//	Try to set each one
		try
		{
			foreach ( $options as $_key => $_value )
			{
				try
				{
					//	See if __set has a better time with this...
					if ( method_exists( $this, 'set' . $_key ) ) $this->{'set' . $_key}( $_value );
					else if ( property_exists( $this, $_key ) ) $this->{$_key} = $_key;
				}
				catch ( Exception $_ex )
				{
					//	Completely ignore errors...
				}
			}
		}
		catch ( Exception $_ex )
		{
			Yii::log( 'Error while loading configuration options: ' . $_ex->getMessage(), 'error', 'yiixl.base' );
		}
	}
}