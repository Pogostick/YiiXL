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
 * @package			yiixl
 * @category		yiixl
 * @since			v1.0.0
 *
 * @filesource
 */

//	Requirements
require_once __DIR__ . '/../YiiXL.php';

/**
 * xlComponent class
 * This is the base class for all YiiXL library objects. It extends the base
 * functionality of the Yii Framework without replacing core code.
 *
 * The two enhancements are:
 * 1. Introduces a new property called $debugLevel that allows fine-tuning of debug and tracing
 * 2. Has a constructor that accepts an array of configuration parameters.
 *
 * @property integer $debugLevel A user-defined debugging level
 * @property array $options The options passed to this object during construction
 */
class xlComponent extends CComponent implements xlIComponent
{
	//********************************************************************************
	//* Constants
	//********************************************************************************

	/**
	 * Our logging tag
	 */
	const
		CLASS_LOG_TAG = 'yiixl.core.components.xlComponent'
	;

	//********************************************************************************
	//* Properties
	//********************************************************************************

	/**
	 * @var integer $_debugLevel User-defined debug flag. false = OFF, anything else is up to you.
	 */
	protected $_debugLevel = false;
	/**
	 * @var array $_options Our configuration options
	 */
	protected $_options;

	//********************************************************************************
	//* Public Methods
	//********************************************************************************

	/**
	 * Constructs a component.
	 * All components accept an array of configuration options. These options are placed into accessible
	 * members (via "setter"). The entire array is stored in the member {@see xlIComponent::options}.
	 * @param array $options
	 * @return \xlComponent
	 */
	public function __construct( $options = array() )
	{
		//	Initialize...
		$this->_options = array();

		//	Set any properties via standard config array
		xlOptions::loadConfiguration( $this, $options );
	}

	/**
	 * Initialize the component
	 * @return bool|void
	 */
	public function init()
	{
		//	An empty init for consistency.
		return true;
	}

	/**
	 * Gets a single configuration option
	 * @param string $key
	 * @param mixed $defaultValue
	 * @param bool $unsetAfter
	 * @return mixed
	 */
	public function getOption( $key, $defaultValue = null, $unsetAfter = false )
	{
		return XL::o( $this->_options, $key, $defaultValue, $unsetAfter );
	}

	/**
	 * Sets a single configuration option
	 * @param string $key
	 * @param mixed $value
	 * @return \xlComponent
	 */
	public function setOption( $key, $value = null )
	{
		XL::setOption( $this->_options, $key, $value );
		return $this;
	}

	/**
	 * Loads the configuration options
	 * @param array $options
	 * @return xlIBaseComponent
	 */
	public function loadOptions( $options = array() )
	{
		$this->_options = $options;
		return $this;
	}

	/**
	 * @param string $name
	 * @param array $parameters
	 * @return mixed
	 */
	public function __call( $name, $parameters )
	{
		try
		{
			XL::smartCall( $this, $name, $parameters );
		}
		catch ( xlMethodNotFoundException $_ex )
		{
			//	Kick it back up the chain in this case
			return parent::__call( $name, $parameters );
		}

		//	Any other exceptions bubble up
	}

	/**
	 * Attach/detach an event handler.
	 * @param string $eventName
	 * @param callback|null $eventHandler
	 * @param boolean $onOff
	 * @return boolean
	 */
	public function setEventHandler( $eventName, $eventHandler, $onOff = true )
	{
		if ( $onOff )
		{
			$this->getEventHandlers( $eventName )->add( $eventHandler );
			return true;
		}
		else
		{
			if ( $this->hasEventHandler( $eventName ) )
				return ( false !== $this->getEventHandlers( $eventName )->remove( $eventHandler ) );
		}

		return false;
	}

	//********************************************************************************
	//* Private Methods
	//********************************************************************************

	/**
	 * Walks the backtrace to find the calling object
	 * @param string $instanceFilter Only return objects of this instance
	 * @return string
	 */
	protected static function _getCallerObject( $instanceFilter = null )
	{
		$_stack = debug_backtrace( true );

		for ( $_i = 0, $_count = count( $_stack ); $_i < $_count; $_i++ )
		{
			if ( null !== ( $_caller = XL::o( $_stack[$_i], 'object' ) ) )
			{
				if ( null !== $instanceFilter )
				{
					if ( $_caller instanceof $instanceFilter )
						return $_caller;

					continue;
				}

				return $_caller;
			}
		}

		return null;
	}

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
	 * @param bool $value
	 * @return \xlComponent
	 */
	public function setDebugLevel( $value = false )
	{
		$this->_debugLevel = $value;
		return $this;
	}

	/**
	 * Gets the configuration options
	 * @return array
	 */
	public function getOptions()
	{
		return $this->_options;
	}

	/**
	 * Gets the configuration options
	 * @param array $options
	 * @return \xlComponent
	 */
	public function setOptions( $options = array( ) )
	{
		$this->_options = $options;
		return $this;
	}

}