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
 * xlRestModel
 * Provides base functionality for models used in REST applications
 */
class xlRestModel extends xlActiveRecord
{
	//********************************************************************************
	//* Constants
	//********************************************************************************

	/**
	 * @const string Our logging tag
	 */
	const
		CLASS_LOG_TAG = 'yiixl.core.models.xlRestModel';

	//*******************************************************************************
	//* REST Methods
	//*******************************************************************************

	/**
	 * If a model has a REST mapping, attributes are mapped an returned in an array.
	 * @return array|null The resulting view
	 */
	public function getRestAttributes()
	{
		if ( method_exists( $this, 'attributeRestMap' ) )
		{
			$_resultList = array();
			$_columnList = $this->getSchema();

			foreach ( $this->attributeRestMap() as $_key => $_value )
			{
				$_attributeValue = $this->getAttribute( $_key );

				//	Apply formats
				switch ( $_columnList[$_key]->dbType )
				{
					case 'date':
					case 'datetime':
					case 'timestamp':
						//	Handle blanks
						if ( null !== $_attributeValue && $_attributeValue != '0000-00-00' && $_attributeValue != '0000-00-00 00:00:00' ) $_attributeValue = date( 'c', strtotime( $_attributeValue ) );
						break;
				}

				$_resultList[$_value] = $_attributeValue;
			}

			return $_resultList;
		}

		return null;
	}

	/**
	 * Sets the values in the model based on REST attribute names
	 * @param array $attributeList
	 */
	public function setRestAttributes( $attributeList )
	{
		if ( method_exists( $this, 'attributeRestMap' ) )
		{
			CPSLog::trace( __METHOD__, '  - Setting REST attributes' );

			$_map = $this->attributeRestMap();

			foreach ( $attributeList as $_key => $_value )
			{
				if ( false !== ( $_mapKey = array_search( $_key, $_map ) ) ) $this->setAttribute( $_mapKey, $_value );
			}

			CPSLog::trace( __METHOD__, '  - REST attributes set' );
		}
	}

}
