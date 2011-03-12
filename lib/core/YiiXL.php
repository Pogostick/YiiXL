<?php
/**
 * This file is part of the YiiXL package.
 *
 * @copyright Copyright (c) 2009-2011 Pogostick, LLC.
 * @link http://www.pogostick.com Pogostick, LLC.
 * @license http://www.pogostick.com/licensing
 * @author Jerry Ablan <jablan@pogostick.com>
 * @package yiixl
 * @subpackage core
 * @filesource
 */

//	Throw in a convenience alias
Yii::setPathOfAlias( 'yiixl', '/usr/local/yiixl/lib' );

//	Required interfaces & components
require_once dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'YiiXLBase.php';

/**
 * Main YiiXL entry point
 */
class YiiXL extends YiiXLBase
{
	//	Conveeeeeeeeniencssssssssssssseeeee
}

/**
 * Alias for YiiXL (three characters less typing FTW!)
 */
class XL extends YiiXL
{
	//	Even more convenienter
}