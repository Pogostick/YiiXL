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
 * @package			yiixl.core.Models
 * @category		Models
 * @since			v1.0.0
 *
 * @filesource
 */
/**
 * xlActiveRecord
 * Provides an enhanced CActiveRecord object
 *
 * @property string $modelClass The class name of the model
 */
class xlActiveRecord extends CActiveRecord
{
	//********************************************************************************
	//* Constants
	//********************************************************************************

	/**
	 * @const string Our logging tag
	 */
	const
		CLASS_LOG_TAG = 'yiixl.core.models.xlActiveRecord';

	//*******************************************************************************
	//* Private Members
	//*******************************************************************************

	/**
	 * @var string
	 */
	protected $_modelClass = null;
	/**
	 * @var CDbTransaction[]
	 */
	protected static $_transactionStack = array();

	//*******************************************************************************
	//* Public Methods
	//*******************************************************************************

	/**
	 * Builds a xlActiveRecord and sets the model name
	 * @param string $scenario
	 * @return xlActiveRecord
	 */
	public function __construct( $scenario = 'insert' )
	{
		parent::__construct( $scenario );
		$this->_modelClass = ( version_compare( PHP_VERSION, '5.3.0' ) > 0 ) ? get_called_class() : get_class( $this );
	}

	/**
	 * Checks if a component has an attached behavior
	 * @param string $className
	 * @return boolean
	 */
	public function hasBehavior( $className )
	{
		//	Look for behaviors
		foreach ( $this->behaviors() as $_column => $_behaviors )
		{
			if ( null !== ( $_class = XL::o( $_behaviors, 'class' ) ) )
			{
				$_class = Yii::import( $_class );
			}

			//	Check...
			if ( $className == $_column || $className == $_class )
			{
				return true;
			}
		}

		//	Nope!
		return false;
	}

	/**
	 * Returns the errors on this model in a single string suitable for logging.
	 * @param string $attribute Attribute name. Use null to retrieve errors for all attributes.
	 * @param bool $lineBreaks
	 * @return string
	 */
	public function getErrorsForLogging( $attribute = null, $lineBreaks = true )
	{
		$_result = null;
		$_i = 1;

		$_errors = $this->getErrors( $attribute );

		if ( ! empty( $_errors ) )
		{
			foreach ( $_errors as $_attribute => $_error )
			{
				$_result .= $_i++ . '. [' . $_attribute . '] : ' . implode( '|', $_error ) . ( $lineBreaks ? PHP_EOL : null );
			}
		}

		return $_result;
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions. Override for more specific search criteria.
	 * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
	 */
	public function search()
	{
		$_criteria = new CDbCriteria();
		$_columns = $this->getTableSchema()->columns;

		/** @var $_column CDbColumnSchema */
		foreach ( $_columns as $_column )
		{
			if ( 'string' == strtolower( $_column->type ) )
			{
				$_criteria->compare(
					$_column->name,
					$this->{$_column->name},
					true
				);
			}
		}

		return new CActiveDataProvider(
			$this->_modelClass,
			array(
				 'criteria' => $_criteria
			)
		);
	}

	//*******************************************************************************
	//* Transaction Management
	//*******************************************************************************

	/**
	 * Checks to see if there are any transactions going...
	 * @return boolean
	 */
	public function hasTransaction()
	{
		return ( 0 != count( self::$_transactionStack ) );
	}

	/**
	 * Begins a database transaction (PHP 5.3+ only)
	 * @return CDbTransaction
	 * @throws CDbException
	 */
	public static function beginTransaction()
	{
		/** @var $_transaction CDbTransaction */
		$_transaction = null;

		if ( version_compare( PHP_VERSION, '5.3.0' ) > 0 )
		{
			$_modelClass = get_called_class();
			/** @var $_model xlActiveRecord */
			$_model = new $_modelClass;
			$_transaction = $_model->getDbConnection()->beginTransaction();
			array_push( self::$_transactionStack, $_transaction );
		}

		return $_transaction;
	}

	/**
	 * Commits the transaction at the top of the stack, if any.
	 * @return bool
	 */
	public static function commitTransaction()
	{
		/** @var $_transaction CDbTransaction */
		if ( null !== ( $_transaction = array_pop( self::$_transactionStack ) ) )
		{
			return $_transaction->commit();
		}

		return false;
	}

	/**
	 * Rolls back the current transaction, if any... and throws (or re-throws) $exception if provided.
	 * @param \Exception|null $exception
	 * @throws CDbException
	 */
	public static function rollbackTransaction( Exception $exception = null )
	{
		/** @var $_transaction CDbTransaction */
		if ( null !== ( $_transaction = array_pop( self::$_transactionStack ) ) )
		{
			$_transaction->rollback();
		}

		//	Throw it if given
		if ( null !== $exception )
		{
			throw $exception;
		}
	}

	//*******************************************************************************
	//* Event Handlers
	//*******************************************************************************

	/**
	 * Grab our name
	 * @return boolean
	 */
	public function afterConstruct()
	{
		if ( null === $this->_modelClass )
		{
			$this->_modelClass = ( version_compare( PHP_VERSION, '5.3.0' ) > 0 ) ? get_called_class() : get_class( $this );
		}

		return parent::afterConstruct();
	}

	/**
	 * Fix broken Yii model
	 * @return array
	 * @see xlActiveRecord::attributeLabels
	 */
	public function getAttributeLabels()
	{
		return $this->attributeLabels();
	}

	/**
	 * Override of CModel::setAttributes Populates member variables as well. Aware of Yii 1.1.0+. NOTE: This method ignores Yii's "safe" attribute nonsense.
	 * @param array $attributes
	 * @return bool
	 */
	public function setAttributes( $attributes )
	{
		if ( ! is_array( $attributes ) )
		{
			return false;
		}

		$_attributeNames = array_flip( $this->attributeNames()  );

		if ( version_compare( Yii::getVersion(), '1.1.0', '>=' ) )
		{
			foreach ( $attributes as $_column => $_value )
			{
				if ( isset( $_attributeNames[$_column] ) )
				{
					$this->setAttribute( $_column, $_value );
				}
				else
				{
					if ( $this->hasProperty( $_column ) && $this->canSetProperty( $_column ) )
					{
						$this->{$_column} = $_value;
					}
				}
			}
		}
		else
		{
			foreach ( $attributes as $_column => $_value )
			{
				if ( isset( $_attributeNames[$_column] ) || ( $this->hasProperty( $_column ) && $this->canSetProperty( $_column ) ) )
				{
					$this->setAttribute( $_column, $_value );
				}
			}
		}

		return true;
	}

	//*******************************************************************************
	//* PHP 5.3+ Awesomeness!
	//*******************************************************************************

	/**
	 * Returns a model for the currently called class
	 * @static
	 * @return xlActiveRecord
	 */
	protected static function _model()
	{
		/** @var $_class xlActiveRecord */
		$_class = ( version_compare( PHP_VERSION, '5.3.0' ) > 0 ) ? get_called_class() : get_class();
		return $_class::model();
	}

	/**
	 * Finds a single active record with the specified condition.
	 * @param mixed $condition query condition or criteria.
	 * If a string, it is treated as query condition (the WHERE clause);
	 * If an array, it is treated as the initial values for constructing a {@link CDbCriteria} object;
	 * Otherwise, it should be an instance of {@link CDbCriteria}.
	 * @param array $params parameters to be bound to an SQL statement.
	 * This is only used when the first parameter is a string (query condition).
	 * In other cases, please use {@link CDbCriteria::params} to set parameters.
	 * @return CActiveRecord the record found. Null if no record is found.
	 */
	public static function xlFind( $condition = '', $params = array() )
	{
		return self::_model()->find( $condition, $params );
	}

	/**
	 * Finds all active records satisfying the specified condition.
	 * See {@link find()} for detailed explanation about $condition and $params.
	 * @param mixed $condition query condition or criteria.
	 * @param array $params parameters to be bound to an SQL statement.
	 * @return array list of active records satisfying the specified condition. An empty array is returned if none is found.
	 */
	public static function xlFindAll( $condition = '', $params = array() )
	{
		return self::_model()->findAll( $condition, $params );
	}

	/**
	 * Finds a single active record with the specified primary key.
	 * See {@link find()} for detailed explanation about $condition and $params.
	 * @param mixed $condition query condition or criteria.
	 * @param array $params parameters to be bound to an SQL statement.
	 * @return CActiveRecord the record found. Null if none is found.
	 */
	public static function xlFindByPk( $condition = '', $params = array() )
	{
		return self::_model()->findByPk( $condition, $params );
	}

	/**
	 * Finds all active records with the specified primary keys.
	 * See {@link find()} for detailed explanation about $condition and $params.
	 * @param $key
	 * @param mixed $condition query condition or criteria.
	 * @param array $params parameters to be bound to an SQL statement.
	 * @return array the records found. An empty array is returned if none is found.
	 */
	public static function xlFindAllByPk( $key, $condition = '', $params = array() )
	{
		return self::_model()->findAllByPk( $key, $condition, $params );
	}

	/**
	 * Finds a single active record that has the specified attribute values.
	 * See {@link find()} for detailed explanation about $condition and $params.
	 * @param array $attributes list of attribute values (indexed by attribute names) that the active records should match.
	 * Since version 1.0.8, an attribute value can be an array which will be used to generate an IN condition.
	 * @param mixed $condition query condition or criteria.
	 * @param array $params parameters to be bound to an SQL statement.
	 * @return CActiveRecord the record found. Null if none is found.
	 */
	public static function xlFindByAttributes( $attributes, $condition = '', $params = array() )
	{
		return self::_model()->findByAttributes( $attributes, $condition, $params );
	}

	/**
	 * Finds all active records that have the specified attribute values.
	 * See {@link find()} for detailed explanation about $condition and $params.
	 * @param array $attributes list of attribute values (indexed by attribute names) that the active records should match.
	 * Since version 1.0.8, an attribute value can be an array which will be used to generate an IN condition.
	 * @param mixed $condition query condition or criteria.
	 * @param array $params parameters to be bound to an SQL statement.
	 * @return array the records found. An empty array is returned if none is found.
	 */
	public static function xlFindAllByAttributes( $attributes, $condition = '', $params = array() )
	{
		return self::_model()->findAllByAttributes( $attributes, $condition, $params );
	}

	/**
	 * Finds a single active record with the specified SQL statement.
	 * @param string $sql the SQL statement
	 * @param array $params parameters to be bound to the SQL statement
	 * @return CActiveRecord the record found. Null if none is found.
	 */
	public static function xlFindBySql( $sql, $params = array() )
	{
		return self::_model()->findBySql( $sql, $params );
	}

	/**
	 * Finds all active records using the specified SQL statement.
	 * @param string $sql the SQL statement
	 * @param array $params parameters to be bound to the SQL statement
	 * @return array the records found. An empty array is returned if none is found.
	 */
	public static function xlFindAllBySql( $sql, $params = array() )
	{
		return self::_model()->findAllBySql( $sql, $params );
	}

}
