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
 * @subpackage core
 *
 * @filesource
 */
/*! \mainpage YiiXL - The Yii Extension Library Of Doom!
 * \section intro_sec YiiXL
 * Visit <a href="http://yiixl.com">YiiXL web site</a> to find more information about this project.
 * \section install_sec Installation
 * \subsection step1 Install
 * \subsection step2 Configure
 * @code
 * 		Yii::setPathOfAlias( 'yiixl', '/path/to/your/install' );
 * @endcode
 * \subsection step3 Import what you want!
 * @code
 * 		'import' => array(
 * 			...
 * 			...
 * 			//	YiiXL
 * 			'yiixl.core.*',
 * 			'yiixl.ui.jquery.*',
 * 			...
 * 			...
 * 		),
 * @endcode
 * \subsection step4 Code Responsibly!
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
}

/**
 * Alias for YiiXL (three characters less typing FTW!)
 */
class XL extends YiiXL
{
}