<?php
$_yii = '/usr/local/yii/framework/yiit.php';
$_config = 'config/testConfig.php';

require_once( $_yii );
require_once( dirname( __FILE__ ) . '/WebTestCase.php' );

Yii::createWebApplication( $_config );
