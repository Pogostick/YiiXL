<?php
/*
 * This file was generated by the psYiiExtensions scaffolding package.
 * 
 * @copyright Copyright &copy; 2009 My Company, LLC.
 * @link http://www.example.com
 */

/**
 * create view file
 * 
 * @package 	blog
 * @subpackage 	
 * 
 * @author 		Web Master <webmaster@example.com>
 * @version 	SVN: $Id: create.php 367 2010-01-16 04:29:24Z jerryablan@gmail.com $
 * @since 		v1.0.6
 *  
 * @filesource
 * 
 */
	echo CPSForm::formHeaderEx( 'New Comment', array( 'menuButtons' => array( 'save', 'cancel' ) ) );
	
	echo $this->renderPartial( '_form', array(
		'model' => $model,
		'update' => false,
));