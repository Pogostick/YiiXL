<?php
//	Requirements
require_once __DIR__ . DIRECTORY_SEPARATOR . 'rb.php';
//require_once __DIR__ . DIRECTORY_SEPARATOR . 'xlRedBeanModel.php';

/**
 * xlRedBean
 * A convenience class intended to integrate RedBean with Yii's ActiveRecord
 *
 * @note Requires PHP v5.3+
 */
class xlRedBean extends xlComponent
{
	//*************************************************************************
	//* Private Members
	//*************************************************************************

	/**
	 * @staticvar RedBean_OODB
	 */
	protected static $_rbInstance;
	/**
	 * @staticvar RedBean_ToolBox
	 */
	protected static $_rbTools;
	/**
	 * @var string
	 */
	protected $_className;
	/**
	 * @var RedBean_OODBBean
	 */
	protected $_thisBean;
	/**
	 * @var int
	 */
	protected $_objectId;

	//*************************************************************************
	//* Public Methods
	//*************************************************************************

	/**
	 * Constructor.
	 *
	 * @param RedBean_OODBBean $rbObject
	 * @param string $_className
	 */
	public function __construct( $rbObject = null, $_className = null )
	{
		//	Get our instance
		self::$_rbInstance = self::getInstance();

		if ( null !== $rbObject && null !== $_className )
		{
			$this->_className = $_className;
			$this->_thisBean = $rbObject;
		}
		else
		{
			$this->_className = get_class( $this );
			$this->_thisBean = self::$_rbInstance->dispense( strtolower( $this->_className ) );
		}
	}

	/**
	 * Creates and returns a RedBean database instance using the default configured database.
	 * You can override the database component to use by setting the 'redBeanDatabaseName' parameter
	 * in your configuration file.
	 * @return RedBean_OODB
	 */
	protected static function getInstance()
	{
		//	Don't do it twice...
		if ( null !== self::$_rbInstance )
			return self::$_rbInstance;

		//	Get the database to use with RedBean
		$_db = XL::_gco( XL::_gp( 'redBeanDatabaseName', 'db' ) );
		self::$_rbTools = kickstart( $_db->getPdoInstance(), null, null, XL::_gp( 'readBeanDatabaseFrozen', false ) );

		//	Return the instance
		return self::$_rbTools->getRedBean();
	}

	/**
	 * Returns the current database connection.
	 * @return RedBean_Adapter_DBAdapter
	 */
	protected static function getDbConnection()
	{
		return self::getInstance()->getDatabaseAdapter();
	}

	/**
	 * Returns an instance of the class the static method was called on.
	 * That means if you create a class named "Car" you will receive an
	 * instance of "Car" when calling "Car::load();" if an item with the
	 * provided id is persisted in the database.
	 *
	 * @param integer $id The primary key
	 * @return mixed
	 */
	public static function load( $id )
	{
		$_className = strtolower( get_called_class() );
		$_object = self::$_rbInstance->load( $_className, $id );
		return new $_className( $_object, $_className );
	}

	/**
	 * A proxy method to tunnel all set operations to the embedded RedBean
	 * object.
	 * @param string $name
	 * @param mixed $value
	 */
	public function __set( $name, $value )
	{
		$this->_thisBean->{$name} = $value;
	}


	/**
	 * A proxy method to tunnel all get operations to the embedded RedBean
	 * object.
	 * @param string $name
	 * @return mixed
	 */
	public function __get( $name )
	{
		return $this->_thisBean->{$name};
	}


	/**
	 * Persists the current object to the database.
	 * @return integer The saved item's database id.
	 */
	public function save()
	{
		$this->_objectId = self::$_rbInstance->store( $this->_thisBean );
		return $this->_objectId;
	}

	/**
	 * Deletes the currently loaded object
	 */
	public function delete()
	{
		self::$_rbInstance->trash( $this->_thisBean );
	}
}

//*************************************************************************
//* Plugin Autoloader
//*************************************************************************

/**
 * Autoloader for this plugin
 * @param string $className
 */
function _xlRedBeanAutoLoader_( $className )
{
	$_classFile = XL::_gpoa( 'application.models' ) . DIRECTORY_SEPARATOR . $className . '.php';

	if ( file_exists( $_classFile ) )
		require $_classFile;
}

spl_autoload_register( '_xlReadBeanAutoLoader_' );