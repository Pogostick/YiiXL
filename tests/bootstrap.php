<?php
$_yii = '/usr/local/yii/framework/yiit.php';
$_config = __DIR__ . '/config/testConfig.php';

require_once( $_yii );
require_once( __DIR__ . '/WebTestCase.php' );

Yii::createWebApplication( $_config );
