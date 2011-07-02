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
 * @package			yiixl.core.components
 * @category		Events
 * @since			v1.0.0
 *
 * @brief
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
require_once __DIR__ . '/YiiXLBase.php';
