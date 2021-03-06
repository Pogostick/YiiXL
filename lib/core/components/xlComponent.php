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
 * @package			yiixl.core
 * @since			v1.0.0
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

	/***
	 * @var boolean Automatically searches class for event handler signatures and attachs them
	 */
	protected $_autoAttachEvents = true;
	/**
	 * @var xlBehavior[] This component's behaviors
	 */
	protected $_behaviors;
	/**
	 * @var integer $_debugLevel User-defined debug flag. false = OFF, anything else is up to you.
	 */
	protected $_debugLevel = false;
	/**
	 * @var bool If true, exceptions will be logged to PHP error_log
	 */
	protected $_enableLogging = false;
	/**
	 * @var CList[] This component's events
	 */
	protected $_events;
	/**
	 * @var SplStack|array
	 */
	protected $_exceptionStack;
	/**
	 * @var array $_options Our configuration options
	 */
	protected $_options;
	/**
	 * @var Closure[] This component's triggers
	 */
	protected $_triggers;
	/**
	 * @var bool If true, undefined properties will be added to the object
	 */
	protected $_autoAddMembers = true;


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

		//	If SplStack is available, use it!
		if ( @class_exists( 'SplStack' ) )
		{
			$this->_exceptionStack = new SplStack();
		}
		else
		{
			$this->_exceptionStack = array();
		}

		//	Set any properties via standard config array
		xlOptions::loadConfiguration( $this, $options );

		//	Initialize our structures if not already
		$this->_behaviors = ( null === $this->_behaviors ) ? : array();
		$this->_events = ( null === $this->_events ) ? : new ArrayObject();
		$this->_options = ( null === $this->_options ) ? : array();
		$this->_triggers = ( null === $this->_triggers ) ? : array();

		//	Attach any event handlers we find if desired...
		if ( $this->_autoAttachEvents )
		{
			$this->_autoAttachEventHandlers();
		}

		//	Trigger our afterConstruct event
		$this->raiseEvent( 'afterConstruct' );
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

	//*************************************************************************
	//* Magic Overrides 
	//*************************************************************************
	
	/**
	 * returns a property value, an event handler list or a behavior based on its name.
	 * do not call this method. this is a php magic method that we override
	 * to allow using the following syntax to read a property or obtain event handlers:
	 * <pre>
	 * 		$value = $component->propertyName;
	 * 		$handlers = $component->eventName;
	 * </pre>
	 * @param string $name the property name or event name
	 * @return mixed the property value, event handlers attached to the event, or the named behavior (since version 1.0.2)
	 * @throws CException if the property or event is not defined
	 * @see __set
	 */
	public function __get( $name )
	{
		$_getter = 'get' . $name;
		
		//	Try a getter
		if ( method_exists( $this, $_getter ) )
		{
			return $this->{$_getter}();
		}

		//	Try events, ignore errors
		try
		{
			return $this->getEventHandlers( $name );
		}
		catch ( Exception $_ex )
		{
			//	No event!
		}
		
		//	Is there are behavior?
		if ( isset( $this->_behaviors, $this->_behaviors[$name] ) )
		{
			return $this->_behaviors[$name];
		}

		//	Check the methods within each behavior
		if ( is_array( $this->_behaviors ) )
		{
			foreach ( $this->_behaviors as $_object )
			{
				if ( $_object->getEnabled() && ( property_exists( $_object, $name ) || $_object->canGetProperty( $name ) ) )
				{
					return $_object->{$name};
				}
			}
		}

		//	Auto-add...
		if ( $this->_autoAddMembers )
		{
			$_member = '_' . $name;

			if ( isset( $this->{$_member} ) )
			{
				return $this->{$_member};
			}

			//	Not there, add it and move on
			$this->{$_member} = null;
			return $this->{$_member};
		}

		//	Ok, bail
		throw new xlInvalidOptionException(
			sprintf(
				'Property "%s.%s" is not defined.',
				get_class( $this ),
				$name
			)
		);
	}

	/**
	 * Sets value of a component property.
	 * Do not call this method. This is a PHP magic method that we override
	 * to allow using the following syntax to set a property or attach an event handler
	 * <pre>
	 * $this->propertyName=$value;
	 * $this->eventName=$callback;
	 * </pre>
	 * @param string $name the property name or the event name
	 * @param mixed $value the property value or callback
	 * @return xlIComponent|CComponent
	 */
	public function __set( $name, $value )
	{
		$setter = 'set' . $name;

		if ( method_exists( $this, $setter ) )
		{
			return $this->{$setter}( $value );
		}

		if ( 0 === strncasecmp( $name, 'on', 2 ) && method_exists( $this, $name ) )
		{
			$name = strtolower( $name );

			if ( !isset( $this->_events[$name] ) )
			{
				$this->_events[$name] = new CList();
			}

			return $this->_events[$name]->add( $value );
		}

		if ( is_array( $this->_behaviors ) )
		{
			foreach ( $this->_behaviors as $_object )
			{
				if ( $_object->getEnabled() && ( property_exists( $_object, $name ) || $_object->canSetProperty( $name ) ) )
				{
					return $_object->$name = $value;
				}
			}
		}

		//	If there is a getter, then this is a read-only property
		if ( method_exists( $this, 'get' . $name ) )
		{
			throw new xlInvalidOptionException(
				sprintf(
					'Property "%s.%s" is read-only.',
					get_class( $this ),
					$name
				)
			);
		}

		//	Auto-add...
		if ( $this->_autoAddMembers )
		{
			$_member = '_' . $name;

			if ( isset( $this->{$_member} ) )
			{
				$this->{$_member} = $value;
				return $this;
			}

			//	Not there, add it and move on
			$this->{$_member} = null;
			return $this;
		}

		//	No clue about this
		throw new xlInvalidOptionException(
			sprintf(
				'Property "%s.%s" is not defined.',
				get_class( $this ),
				$name
			)
		);
	}

	/**
	 * Checks if a property value is null.
	 * Do not call this method. This is a PHP magic method that we override
	 * to allow using isset() to detect if a component property is set or not.
	 * @param string $name the property name or the event name
	 * @return bool
	 * @since 1.0.1
	 */
	public function __isset( $name )
	{
		$_getter = 'get' . $name;

		if ( method_exists( $this, $_getter ) )
		{
			return $this->{$_getter}() !== null;
		}
		else
		{
			if ( strncasecmp( $name, 'on', 2 ) === 0 && method_exists( $this, $name ) )
			{
				$name = strtolower( $name );
				return isset( $this->_events[$name] ) && $this->_events[$name]->getCount();
			}
			else
			{
				if ( is_array( $this->_behaviors ) )
				{
					if ( isset( $this->_behaviors[$name] ) )
					{
						return true;
					}
					foreach ( $this->_behaviors as $object )
					{
						if ( $object->getEnabled() && ( property_exists( $object, $name ) || $object->canGetProperty( $name ) ) )
						{
							return true;
						}
					}
				}
			}
		}

		return false;
	}

	/**
	 * Sets a component property to be null.
	 * Do not call this method. This is a PHP magic method that we override
	 * to allow using unset() to set a component property to be null.
	 * @param string $name the property name or the event name
	 * @return mixed
	 * @throws xlInvalidOptionException if the property is read only.
	 */
	public function __unset( $name )
	{
		$_setter = 'set' . $name;

		if ( method_exists( $this, $_setter ) )
		{
			$this->{$_setter}( null );
		}
		else
		{
			if ( 0 === strncasecmp( $name, 'on', 2 ) && method_exists( $this, $name ) )
			{
				unset( $this->_events[strtolower( $name )] );
			}
			else
			{
				if ( is_array( $this->_behaviors ) )
				{
					if ( isset( $this->_behaviors[$name] ) )
					{
						$this->detachBehavior( $name );
					}
					else
					{
						foreach ( $this->_behaviors as $_object )
						{
							if ( $_object->getEnabled() )
							{
								if ( property_exists( $_object, $name ) )
								{
									return $_object->{$name} = null;
								}
								else
								{
									if ( $_object->canSetProperty( $name ) )
									{
										return $_object->{$_setter}( null );
									}
									else
									{
										throw new xlInvalidOptionException( 'Property "' . get_class( $this ) . '.' . $name . '" is read only.' );
									}
								}
							}
						}
					}
				}
				else
				{
					if ( method_exists( $this, 'get' . $name ) )
					{
						throw new CException( Yii::t( 'yii', 'Property "{class}.{property}" is read only.',
								array( '{class}' => get_class( $this ), '{property}' => $name ) ) );
					}
				}
			}
		}
		
		return $this;
	}

	/**
	 * Calls the named method which is not a class method.
	 * Do not call this method. This is a PHP magic method that we override
	 * to implement the behavior feature.
	 * @param $method
	 * @param $arguments
	 * @return mixed the method return value
	 */
	public function __call( $method, $arguments )
	{
		//	Call behavior methods if they exist
		foreach ( $this->_behaviors as $_behavior )
		{
			if ( $_behavior->getEnabled() && method_exists( $_behavior, $method ) )
			{
				return call_user_func_array( array( $_behavior, $method ), $arguments );
			}
		}

		if ( $this->canGetProperty( $method ) && class_exists( 'Closure', false ) && $this->{$method} instanceof Closure )
		{
			return call_user_func_array( $this->{$method}, $arguments );
		}

		throw new xlEventException(
			sprintf(
				'%s does not have a method named "%s".',
				get_class( $this ),
				$method
			)
		);
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

	/**
	 * Retrieves the next exception off the stack
	 * @return Exception
	 */
	public function popException()
	{
		if ( $this->_exceptionStack instanceof SplStack )
		{
			return $this->_exceptionStack->pop();
		}

		return array_pop( $this->_exceptionStack );
	}

	/**
	 * @param Exception $exception
	 * @param bool $logToError
	 * @return \xlCComponent $this
	 */
	public function pushException( $exception, $logToError = false )
	{
		if ( $this->_exceptionStack instanceof SplStack )
		{
			$this->_exceptionStack->push( $exception );
		}
		else
		{
			array_push( $this->_exceptionStack, $exception );
		}

		if ( $logToError )
		{
			xlLog::error( 'Exception: ' . $exception->getMessage() );
		}

		return $this;
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

	/**
	 * Returns the named behavior object.
	 * The name 'asa' stands for 'as a'.
	 * @param string $behavior the behavior name
	 * @return xlIBehavior the behavior object, or null if the behavior does not exist
	 * @since 1.0.2
	 */
	public function asa( $behavior )
	{
		return xl::o( $this->_behaviors, $behavior );
	}

	/**
	 * Attaches a list of behaviors to the component. Each behavior is indexed by its name and should be an instance of {@link xlIBehavior},
	 * @param array $behaviors list of behaviors to be attached to the component
	 * @return \xlCComponent
	 */
	public function attachBehaviors( $behaviors = array() )
	{
		foreach ( $behaviors as $_name => $_behavior )
		{
			$this->attachBehavior( $_name, $_behavior );
		}

		return $this;
	}

	/**
	 * Detaches all behaviors from this component.
	 * @return \xlCComponent
	 */
	public function detachBehaviors()
	{
		if ( ! empty( $this->_behaviors ) )
		{
			foreach ( $this->_behaviors as $_name => $_behavior )
			{
				$this->detachBehavior( $_name );
			}

			unset( $this->_behaviors );

			$this->_behaviors = array();
		}

		return $this;
	}

	/**
	 * Attaches a behavior to this component.
	 * @param string $name the behavior's name. It should uniquely identify this behavior.
	 * @param xlIBehavior|array $behaviorConfig
	 * @return xlIBehavior the behavior object
	 */
	public function attachBehavior( $name, $behaviorConfig )
	{
		/** @var $_behavior xlIBehavior */
		$_behavior = $behaviorConfig;

		if ( is_array( $behaviorConfig ) )
		{
			$_behavior = xl::createComponent( $behaviorConfig );
		}

		$_behavior->setEnabled( true );
		$_behavior->attach( $this );

		return $this->_behaviors[$name] = $_behavior;
	}

	/**
	 * Detaches a behavior from the component.
	 * The behavior's {@link xlIBehavior::detach} method will be invoked.
	 * @param string $name the behavior's name. It uniquely identifies the behavior.
	 * @return xlIBehavior the detached behavior. Null if the behavior does not exist.
	 */
	public function detachBehavior( $name )
	{
		/** @var $_behavior xlIBehavior */
		if ( null !== ( $_behavior = xl::o( $this->_behaviors, $name ) ) )
		{
			$_behavior->detach( $this );
			unset( $this->_behaviors[$name] );
		}

		return $_behavior;
	}

	/**
	 * Enables all behaviors attached to this component.
	 */
	public function enableBehaviors()
	{
		if ( $this->_behaviors !== null )
		{
			foreach ( $this->_behaviors as $behavior )
			{
				$behavior->setEnabled( true );
			}
		}
	}

	/**
	 * Disables all behaviors attached to this component.
	 */
	public function disableBehaviors()
	{
		if ( ! empty( $this->_behaviors ) )
		{
			foreach ( $this->_behaviors as $_behavior )
			{
				$_behavior->setEnabled( false );
			}
		}
	}

	/**
	 * Enables a single behavior
	 * @param string $name
	 */
	public function enableBehavior( $name )
	{
		if ( isset( $this->_behaviors[$name] ) )
		{
			$this->_behaviors[$name]->setEnabled( true );
		}
	}

	/**
	 * Disables an attached behavior.
	 * @param string $name
	 */
	public function disableBehavior( $name )
	{
		if ( isset( $this->_behaviors[$name] ) )
		{
			$this->_behaviors[$name]->setEnabled( false );
		}
	}

	/**
	 * Determines whether a property is defined.
	 * A property is defined if there is an accessible getter or setter method
	 * defined in the class.
	 * Note: property names are case-insensitive.
	 * @param string $name the property name
	 * @return boolean whether the property is defined
	 */
	public function hasProperty( $name )
	{
		return $this->canGetProperty( $name ) || $this->canSetProperty( $name );
	}

	/**
	 * Determines whether a property can be read.
	 * A property can be read if the class has a getter method
	 * for the property name. Note, property name is case-insensitive.
	 * @param string $name the property name
	 * @return boolean whether the property can be read
	 * @see canSetProperty
	 */
	public function canGetProperty( $name )
	{
		return method_exists( $this, 'get' . $name );
	}

	/**
	 * Determines whether a property can be set.
	 * A property can be written if the class has a setter method
	 * for the property name. Note, property name is case-insensitive.
	 * @param string $name the property name
	 * @return boolean whether the property can be written
	 * @see canGetProperty
	 */
	public function canSetProperty( $name )
	{
		return method_exists( $this, 'set' . $name );
	}

	/**
	 * Determines whether an event is defined.
	 * Note, event name is case-insensitive.
	 * @param string $name the event name
	 * @return boolean whether an event is defined
	 */
	public function hasEvent( $name )
	{
		return !strncasecmp( $name, 'on', 2 ) && method_exists( $this, $name );
	}

	/**
	 * Checks whether the named event has attached handlers.
	 * @param string $name the event name
	 * @return boolean whether an event has been attached one or several handlers
	 */
	public function hasEventHandler( $name )
	{
		$name = trim( strtolower( $name ) );
		return isset( $this->_events[$name] ) && ! empty( $this->_events[$name] );
	}

	/**
	 * Attaches an event handler to an existing event.
	 * An event handler must be a valid PHP callback, i.e., is_callable returns true.
	 * @param string $eventName
	 * @param callback|Closure $eventHandler
	 * @throws xlEventException if the event is not defined
	 * @see detachEventHandler
	 */
	public function attachEventHandler( $eventName, $eventHandler )
	{
		$this->getEventHandlers( $eventName )->append( $eventHandler );
	}

	/**
	 * Detaches an existing event handler.
	 * This method is the opposite of {@link attachEventHandler}.
	 * @param $eventName
	 * @param $eventHandler
	 *
	 * @internal param string $name event name
	 *
	 * @internal param callback $handler the event handler to be removed
	 * @return boolean if the detachment process is successful
	 * @see attachEventHandler
	 */
	public function detachEventHandler( $eventName, $eventHandler )
	{
		$_handlers = $this->getEventHandlers( $eventName );

		if ( $_handlers->offsetExists( $eventHandler ) )
		{
			$_handlers->offsetUnset( $eventHandler );
			return true;
		}

		return false;
	}

	/**
	 * Raises an event.
	 * @param $eventName
	 * @param xlEvent $event the event parameter
	 * @return bool
	 * @throws xlEventException if the event is undefined or an event handler is invalid.
	 */
	public function raiseEvent( $eventName, $event = null )
	{
		$eventName = trim( strtolower( $eventName ) );

		if ( isset( $this->_events[$eventName] ) )
		{
			if ( null === $event )
			{
				$event = new xlEvent( $this );
			}

			foreach ( $this->_events[$eventName] as $_handler )
			{
				if ( is_callable( $_handler ) )
				{
					call_user_func( $_handler, $event );

					if ( $event instanceof xlEvent && false !== $event->getHandled() )
					{
						return true;
					}

					continue;
				}

				throw new xlEventException(
					sprintf(
						'Event "%s.%s" event handler is not callable.',
						get_class( $this ),
						$eventName
					)
				);
			}
		}
		else if ( ! $this->hasEvent( $eventName ) )
		{
			throw new xlEventException(
				sprintf(
					'Event "%s.%s" is undefined.',
					get_class( $this ),
					$eventName
				)
			);
		}

		return false;
	}

	/**
	 * Evaluates a PHP expression or callback under the context of this component.
	 *
	 * Valid PHP callback can be class method name in the form of
	 * array(ClassName/Object, MethodName), or anonymous function (only available in PHP 5.3.0 or above).
	 *
	 * If a PHP callback is used, the corresponding function/method signature should be
	 * <pre>
	 * function foo($param1, $param2, ..., $component) { ... }
	 * </pre>
	 * where the array elements in the second parameter to this method will be passed
	 * to the callback as $param1, $param2, ...; and the last parameter will be the component itself.
	 *
	 * If a PHP expression is used, the second parameter will be "extracted" into PHP variables
	 * that can be directly accessed in the expression. See {@link http://us.php.net/manual/en/function.extract.php PHP extract}
	 * for more details. In the expression, the component object can be accessed using $this.
	 *
	 * @param mixed $expression_ a PHP expression or PHP callback to be evaluated.
	 * @param array $expressionData_ additional parameters to be passed to the above expression/callback.
	 * @return mixed the expression result
	 * @since 1.1.0
	 */
	public function evaluateExpression( $expression_, $expressionData_ = array() )
	{
		if ( is_string( $expression_ ) )
		{
			extract( $expressionData_ );
			return eval( 'return ' . $expression_ . ';' );
		}

		$expressionData_[] = $this;
		return call_user_func_array( $expression_, $expressionData_ );
	}

	//********************************************************************************
	//* Private Methods
	//********************************************************************************

	/**
	 * If any methods exist in this class that appear to be event handlers, they will be
	 * automatically attached to said events.
	 * @return void
	 */
	protected function _autoAttachEventHandlers()
	{
		$_this = new ReflectionClass( get_called_class( $this ) );

		if ( null !== $_this )
		{
			//	Only public/protected methods are checked...
			$_methodList = $_this->getMethods( ReflectionMethod::IS_PUBLIC | ReflectionMethod::IS_PROTECTED );

			/** @var $_method ReflectionMethod */
			foreach ( $_methodList as $_method )
			{
				$_name = $_method->name;

				//	Event handler?
				if ( 0 === strncasecmp( $_name, 'on', 2 ) && method_exists( $this, substr( $_name, 2 ) ) )
				{
					$this->attachEventHandler( $_name, array( $this, substr( $_name, 2 ) ) );
				}
			}
		}
	}

	/**
	 * Loads an array into properties if they exist.
	 * @param array $options
	 */
	protected function _loadConfiguration( $options = array() )
	{
		//	Make a copy for posterity
		$this->_options = $options;

		foreach ( $options as $_option => $_value )
		{
			try
			{
				$_setter = 'set' . $_option;

				if ( method_exists( $this, $_setter ) )
				{
					$this->{$_setter}( $_value );
				}
				else
				{
					throw new xlInvalidOptionException( 'Option "' . $_option . '" is either read-only or non-existent.' );
				}
			}
			catch ( Exception $_ex )
			{
				$this->pushException( $_ex );
			}
		}
	}

	//*************************************************************************
	//* Properties
	//*************************************************************************

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
	 * @param array $events
	 * @return \xlCComponent
	 */
	protected function _setEvents( $events = array() )
	{
		$this->_events = new ArrayObject( $events );
		return $this;
	}

	/**
	 * @return array
	 */
	public function getEvents()
	{
		return $this->_events;
	}

	/**
	 * @param xlIBehavior[] $behaviors
	 * @return \xlCComponent
	 */
	protected function _setBehaviors( $behaviors = array() )
	{
		$this->_behaviors = $behaviors;
		return $this;
	}

	/**
	 * @return xlIBehavior[]
	 */
	public function getBehaviors()
	{
		return $this->_behaviors;
	}

	/**
	 * @return array
	 */
	public function getOptions()
	{
		return $this->_options;
	}

	/**
	 * Similar to {@link SPComponent::getOption} except it will pull a value from a nested array.
	 * @param integer|string $key
	 * @param integer|string $subKey
	 * @param mixed $defaultValue
	 * @param boolean $unsetValue
	 * @return mixed
	 */
	public function getSubOption( $key = null, $subKey = null, $defaultValue = null, $unsetValue = false )
	{
		return xl::oo( $this->_options, $key, $subKey, $defaultValue, $unsetValue );
	}

	/**
	 * @return bool
	 */
	public function getAutoAttachEvents()
	{
		return $this->_autoAttachEvents;
	}

	/**
	 * @param $value
	 * @return \xlCComponent
	 */
	public function setAutoAttachEvents( $value )
	{
		$this->_autoAttachEvents = $value;
		return $this;
	}

	/**
	 * @return bool
	 */
	public function getEnableLogging()
	{
		return $this->_enableLogging;
	}

	/**
	 * @param $value
	 * @return \xlCComponent
	 */
	public function setEnableLogging( $value )
	{
		$this->_enableLogging = $value;
		return $this;
	}

	/**
	 * @return array|\SplStack
	 */
	public function getExceptionStack()
	{
		return $this->_exceptionStack;
	}

	/**
	 * @param $triggers
	 * @return \xlCComponent
	 */
	protected function _setTriggers( $triggers )
	{
		$this->_triggers = $triggers;
		return $this;
	}

	/**
	 * @return array|\Closure[]|null
	 */
	public function getTriggers()
	{
		return $this->_triggers;
	}

	/**
	 * @param boolean $autoAddMembers
	 * @return \xlComponent $this
	 */
	public function setAutoAddMembers( $autoAddMembers )
	{
		$this->_autoAddMembers = $autoAddMembers;
		return $this;
	}

	/**
	 * @return boolean
	 */
	public function getAutoAddMembers()
	{
		return $this->_autoAddMembers;
	}

}
