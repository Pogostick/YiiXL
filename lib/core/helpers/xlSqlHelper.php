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
 * @package			yiixl.core.helpers
 * @category		Helpers
 * @since			v1.0.0
 *
 * @filesource
 */
/**
 * xlSqlHelper
 * Some database helpers
 */
class xlSqlHelper extends xlBaseHelper
{
	//********************************************************************************
	//* Constants
	//********************************************************************************

	/**
	 * @const string Our logging tag
	 */
	const
		CLASS_LOG_TAG = 'yiixl.core.helpers.xlSqlHelper';

	//*************************************************************************
	//* Public Methods 
	//*************************************************************************

	/**
	 * Creates and returns a CDbCommand object from the specified SQL
	 * @param string $sql
	 * @param CDbConnection $dbToUse
	 * @return CDbCommand
	 * @throws xlDatabaseException
	 */
	public static function sql( $sql, $dbToUse = null )
	{
		/** @var $_db CDbConnection */
		if ( null !== ( $_db = XL::nvl( $dbToUse, XL::app()->getDb() ) ) )
		{
			return $_db->createCommand( $sql );
		}
		
		throw new xlDatabaseException( 'No database specified or configured for use.' );
	}

	/**
	 * Convenience method to execute a query (static version)
	 * @param string $sql
	 * @param array $parameters
	 * @param CDbConnection $dbToUse
	 * @return integer The number of rows affected by the operation
	 * @throws xlDatabaseException
	 */
	public static function sqlExecute( $sql, $parameters = array(), $dbToUse = null )
	{
		return self::sql( $sql, $dbToUse )->execute( $parameters );
	}

	/**
	 * Executes the given sql statement and returns all results
	 * @param string $sql
	 * @param array $parameterList List of parameters for call
	 * @param CDbConnection $dbToUse
	 * @return array
	 */
	public static function sqlAll( $sql, $parameterList = array(), $dbToUse = null )
	{
		return self::sql( $sql, $dbToUse )->queryAll( true, $parameterList );
	}

	/**
	 * Executes the given sql statement and returns the first column of the first row ONLY
	 * @param string $sql
	 * @param array $parameterList List of parameters for call
	 * @param CDbConnection $dbToUse
	 * @return array
	 */
	public static function sqlScalar( $sql, $parameterList = array(), $dbToUse = null )
	{
		return self::sql( $sql, $dbToUse )->queryScalar( $parameterList );
	}

	/**
	 * Executes the given sql statement and returns the first column of all rows
	 * @param string $sql
	 * @param array $parameterList List of parameters for call
	 * @param CDbConnection $dbToUse
	 * @return array
	 */
	public static function sqlAllScalar( $sql, $parameterList = null, $dbToUse = null )
	{
		return self::sql( $sql, $dbToUse )->queryColumn( $parameterList );
	}

	/**
	 * Executes the given sql statement and returns the first row ONLY
	 * @param string $sql
	 * @param array $parameterList List of parameters for call
	 * @param CDbConnection $dbToUse
	 * @return array
	 */
	public static function sqlFirstRow( $sql, $parameterList = null, $dbToUse = null )
	{
		return self::sql( $sql, $dbToUse )->queryRow( true, $parameterList );
	}

}
