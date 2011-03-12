<?php
//	Include the common stuff...
require_once dirname( __FILE__ ) . '/common.php';

//	Our configuration array
return array(
	'basePath' => dirname( __FILE__ ) . DIRECTORY_SEPARATOR . '../lib',
	'name' => 'YiiXL Library Tester',

	//	preloading 'log' component
	'preload' => array( 'log' ),

	//	autoloading model and component classes
	'import' => array(
		//	System...
		'application.models.*',
		'application.components.*',
		'application.controllers.*',
		//	YiiXL
//		'yiixl.lib.core.*',
	),

	//	application components
	'components' => array(

		'log' => array(
			'class' => 'CLogRouter',
			'routes' => array(
				array(
					'class' => 'CFileLogRoute',
					'levels' => $_logLevel,
					'maxFileSize' => 10240,
					'maxLogFiles' => 7,
					'logFile' => 'yiixl_tests.log',
					'logPath' => dirname( __FILE__ ) . '/../logs',
				),
			),
		),

		'urlManager' => array(
			'urlFormat' => 'path',
			'showScriptName' => false,
			'rules' => array(
			),
		),

		'errorHandler' => array(
			'errorAction' => 'site/error',
		),
	),

	//	Application-level parameters
	'params' => array(
		'version' => '1.0',
		'adminEmail' => 'jablan@pogostick.com',
		'@copyright' => 'Copyright &copy; ' . date('Y') . ' Pogostick, LLC.',
		'@author' => 'Jerry Ablan <jablan@pogostick.com>',
		'@link' => 'http://www.pogostick.com',
		'@package' => 'yiixl',

		//	UI theme
		'theme' => 'redmond',
	),

);
