<?php
/**
 * This file is part of the YiiXL package.
 * 
 * @copyright Copyright (c) 2009-2011 Pogostick, LLC.
 * @link http://www.pogostick.com Pogostick, LLC.
 * @license http://www.pogostick.com/licensing
 */

/**
 * CXLHelperBase
 * The method name is like 'restXYZ' where 'XYZ' stands for the action name.
 * 
 * @package 	yiixl
 * @subpackage 	base
 * 
 * @author 		Jerry Ablan <jablan@pogostick.com>
 * @version 	SVN $Id: CXLHelperBase
 * @since 		v1.0.6
 * 
 * @filesource
 */
class CXLHelperBase
{
	//********************************************************************************
	//* Public Methods
	//********************************************************************************
	
	/**
	* Runs the REST action.
	* @throws CHttpException
	*/
	public function run()
	{
		$_oController = $this->getController();
		
		if ( ! ( $_oController instanceof IXL
		{
			$_oController->missingAction( $this->getId() );
			return;
		}
		
		//	Call the controllers dispatch method...
		$_oController->dispatchRequest( $this );
	}

}