<?php
/*
 * This file was generated by the yiixl scaffolding package.
 * 
 * @copyright Copyright &copy; 2009 My Company, LLC.
 * @link http://www.example.com
 */

/**
 * Post form file
 * 
 * @package 	blog.views
 * @subpackage 	post
 * 
 * @author 		Web Master <webmaster@example.com>
 * @version 	SVN: $Id: _form.php 362 2010-01-03 05:34:35Z jerryablan@gmail.com $
 * @since 		v1.0.6
 *  
 * @filesource
 * 
 */

	YiiXL::_rcf( Yii::app()->baseUrl . '/css/form.css' );

	//	I don't like this, I prefer bold-faced labels
	YiiXL::$afterRequiredLabel = null;
	
	if ( Yii::app()->user->hasFlash( 'commentSubmitted' ) )
	{
		echo '<div class="yiiForm">' . Yii::app()->user->getFlash( 'commentSubmitted' ) . '</div>';
		return;
	}
	
	$_bPreviewMode = ( isset( $_POST['previewPost'] ) && ! $model->hasErrors() );

	$_arFormOpts = array( 
		//	Gimme jQuery UI styling
		'uiStyle' => YiiXL::UI_JQUERY,
		
		//	Our model
		'formModel' => $model,
		
		//	We want error summary...
		'errorSummary' => true,
		
		//	Our form fields...
		'fields' => array(
			array( 'hidden', $_bPreviewMode ? 'previewPost' : 'submitPost', 1 ),
			array( YiiXL::TEXT, 'title_text', array( 'size' => 60, 'maxlength' => 128 ) ),
			array( YiiXL::TEXT, 'tags_text', array( 'size' => 60, 'maxlength' => 128, 'hint' => 'Separate different tags with commas.' ) ),
			array( YiiXL::DD_GENERIC, 'status_nbr', array( 'data' => Post::model()->getStatusOptions() ) ),
			array( YiiXL::CKEDITOR, 'content_text' ),
		),
		
		//	And validate the form too
		'validate' => true, 
		
		//	Set some validation options
		'validateOptions' => array(
			'messages' => array(
				'Post[title_text]' => array(
					'required' => 'You must give your blog entry a title.',
				),
				'Post[content_text]' => array(
					'required' => 'Your blog entry must have something to read!',
				),
			),
		), 
	);

	//	Show publish/preview only in create mode	
	if ( ! $update )
		$_arFormOpts['fields'][] = array( 'submit', $_bPreviewMode ? 'Preview' : 'Publish', array( 'id' => 'btnSubmit', 'name' => $_bPreviewMode ? 'previewPost' : 'submitPost', 'jqui' => true, 'icon' => 'disk', 'condition' => ! $update ) );
	
	//	Make me a form!
	echo CXLHelperBase
