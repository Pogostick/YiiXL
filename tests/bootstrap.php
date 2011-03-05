<?php
/**
 * bootstrap.php
 *
 * Copyright (c) 2011 Jerry Ablan <jablan@pogostick.com>.
 * \@link http://www.pogostick.com Pogostick, LLC.
 * \@license http://www.pogostick.com/licensing
 *
 * This file is part of yiixl.
 *
 * You may use our open source libraries under the terms of either the MIT
 * License or the Gnu General Public License (GPL) Version 2.
 *
 * The MIT License is recommended for most projects. It is simple and easy to
 * understand, and it places almost no restrictions on what you can do with
 * our code.
 *
 * If the GPL suits your project better, you are also free to use our code
 * under that license.
 *
 * You don’t have to do anything special to choose one license or the other,
 * and you don’t have to notify anyone which license you are using.
 */

//	Include Files
//	Constants
//	Global Settings

/**
 * bootstrap
 *
 * @package 	yiixl
 * @subpackage
 *
 * @author 		Jerry Ablan <jablan@pogostick.com>
 * @version 	SVN $Id$
 * @since 		v1.0.0
 *
 * @filesource
 */

require_once '/usr/local/yii/framework/yii.php';

//	Load our alias
Yii::setPathOfAlias( 'pogostick', dirname( __FILE__ ) . '/../lib' );
Yii::import( 'pogostick.base.*' );
Yii::import( 'pogostick.helpers.*' );