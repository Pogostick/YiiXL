<?php
/*
 * This file is part of yiixl blog example
 * 
 * @copyright Copyright &copy; 2009 Pogostick, LLC
 * @link http://www.pogostick.com Pogostick, LLC.
 * @license http://www.pogostick.com/licensing
 */
/**
 * @package 	yiixl.examples.blog
 * @subpackage 	components
 * 
 * @author 		Jerry Ablan <jablan@pogostick.com>
 * @version 	SVN $Id: UserLogin.php 362 2010-01-03 05:34:35Z jerryablan@gmail.com $
 * @since 		v1.0.6
 * 
 * @filesource
 */
class UserLogin extends CXLHelperBase
{
	//********************************************************************************
	//* Public Methods
	//********************************************************************************
	
	/**
	* Initialize
	* 
	*/
	public function preinit()
	{
		parent::preinit();
		
		$this->title = 'Login';
		$this->autoRender = false;
	}
	
	/**
	* Render our content
	*/
	protected function renderContent()
	{
		$_oForm = new LoginForm();
		
		if ( isset( $_POST, $_POST['LoginForm'] ) )
		{
			$_oForm->attributes = $_POST['LoginForm'];
			
			if ( $_oForm->validate() )
				$this->getController()->refresh();
		}
		
		//	Display the login form
		$this->render( 'userLogin', array( 'form' => $_oForm ) );
	}
 
}