<?php
/**
 * This file is part of the YiiXL package.
 *
 * @copyright Copyright (c) 2009-2011 Pogostick, LLC.
 * @link http://www.pogostick.com Pogostick, LLC.
 * @license http://www.pogostick.com/licensing
 *
 * @package yiixl
 * @subpackage core.helpers
 *
 * @author 		Jerry Ablan <jablan@pogostick.com>
 * @since 		v1.0.6
 *
 * @filesource
 */

/**
 * Hash code/password helpers
 */
class CXLHash implements IXLHelper
{
	//********************************************************************************
	//* Constants
	//********************************************************************************

	/**
	 * Our hash types
	 */
	const
		ALL = 0,
		ALPHA_LOWER = 1,
		ALPHA_UPPER = 2,
		ALPHA = 3,
		ALPHA_NUMERIC = 4,
		ALPHA_LOWER_NUMERIC = 5,
		NUMERIC = 6,
		ALPHA_LOWER_NUMERIC_IDIOTPROOF = 7
	;

	/**
	 * Hashing methods
	 */
	const
		MD5 = 1,
		SHA1 = 2,
		CRC32 = 18
	;

	//********************************************************************************
	//* Member Variables
	//********************************************************************************

	/**
	 * Our hash seeds
	 * @var array
	 */
	protected static $_hashSeeds = array(
		self::ALL => array( 'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', '0', '1', '2', '3', '4', '5', '6', '7', '8', '9' ),
		self::ALPHA_LOWER => array( 'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z' ),
		self::ALPHA_UPPER => array( 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z' ),
		self::ALPHA => array( 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z' ),
		self::ALPHA_NUMERIC => array( 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z', '0', '1', '2', '3', '4', '5', '6', '7', '8', '9' ),
		self::ALPHA_LOWER_NUMERIC => array( 'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z', '0', '1', '2', '3', '4', '5', '6', '7', '8', '9' ),
		self::NUMERIC => array( '0', '1', '2', '3', '4', '5', '6', '7', '8', '9' ),
		self::ALPHA_LOWER_NUMERIC_IDIOTPROOF => array( 'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'j', 'k', 'm', 'n', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z', '2', '3', '4', '5', '6', '7', '8', '9' ),
	);

	//********************************************************************************
	//* Public Methods
	//********************************************************************************

	/**
	 * Our init function, nothing to do here...
	 */
	public static function init()
	{
	}

	/**
	 * Generates a unique hash code
	 *
	 * @param int $hashLength
	 * @return string
	 */
	public static function generate( $hashLength = 20, $hashType = self::ALL )
	{
		//	If we ain't got what you're looking for, return simple md5 hash...
		if ( ! isset( self::$_hashSeeds, self::$_hashSeeds[$hashType] ) || ! is_array( self::$_hashSeeds[$hashType] ) )
			return md5( time() . time() );

		//	Randomly pick elements from the array of seeds
		for ( $_i = 0, $_hash = null, $_size = count( self::$_hashSeeds[$hashType] ) - 1; $_i < $hashLength; $_i++ )
			$_hash .= self::$_hashSeeds[$hashType][mt_rand( 0, $_size )];

		return $_hash;
	}

	/**
	 * Generic hashing method. Will hash any string or generate a random hash and hash that!
	 *
	 * @param string $hashTarget The value to hash..
	 * @param integer $hashType [optional] The type of hash to create. Can be {@see CXLHash::MD5}, {@see CXLHash#SHA1}, or {@link CXLHash#CRC32}. Defaults to {@see CXLHash::SHA1}.
	 * @param integer $hashLength [optional] The length of the hash to return. Only applies if <b>$hashType</b> is not MD5, SH1, or CRC32. . Defaults to 32.
	 * @param boolean $rawOutput [optional] If <b>$rawOutput</b> is true, then the hash digest is returned in raw binary format instead of ASCII.
	 * @return string
	 */
	public static function hash( $hashTarget = null, $hashType = self::SHA1, $hashLength = 32, $rawOutput = false )
	{
		$_value = ( null === $hashTarget ) ? self::generate( $hashLength ) : $hashTarget;

		switch ( $hashType )
		{
			case self::MD5:
				$_hash = md5( $_value, $rawOutput );
				break;

			case self::SHA1:
				$_hash = sha1( $_value, $rawOutput );
				break;

			case self::CRC32:
				$_hash = crc32( $_value );
				break;

			default:
				$_hash = hash( $hashType, $_value, $rawOutput );
				break;
		}

		return $_hash;
	}

}