<?php
//	bootstrap yiixl
Yii::setPathOfAlias( 'yiixl', '/usr/local/yiixl/lib' );

//	Our configuration array
return array(
	'basePath' => dirname(__FILE__) . DIRECTORY_SEPARATOR . '..',
	'name' => 'Pogostick Yii Extensions Tester',
	'defaultController' => 'test',

	// preloading 'log' component
	'preload' => array( 'log' ),

	//	Autoloading model and component classes
	'import' => array(
		'application.models.*',
		'application.components.*',
		'yiixl.core.componets.config.*',
	),

	//	application components
	'components' => array(
		'urlManager' => array(
			'urlFormat' => 'path',
			'showScriptName' => false,
       	),
       	
		//	Authentication manager...
		'authManager' => array(
			'class' => 'CDbAuthManager',
			'connectionID' => 'db',
		),

		'user' => array(
			//	Enable cookie-based authentication
			'allowAutoLogin' => true,
		),

		//      Database (Site)
		'db' => array(
			'class' => 'CDbConnection',
			'autoConnect' => true,
			'connectionString' => 'mysql:host=localhost;dbname=yiixl;',
			'username' => 'pye_user',
			'password' => 'pye_user',
			//'schemaCachingDuration' => 3600,
		),

		'log' => array(
			'class' => 'CLogRouter',
			'routes' => array(
				array(
					'class' => 'CFileLogRoute',
					'levels' => 'info, error, warning, trace',
					'maxFileSize' => '102400',
				),
			),
		),
	),

	//	Our application parameters
	'params' => require( dirname( __FILE__ ) . '/params.php' ),

);
