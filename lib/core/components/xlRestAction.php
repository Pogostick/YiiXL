<?php
/**
 * This file is part of YiiXL
 * Copyright (c) 2009-2011, Pogostick, LLC. All rights reserved.
 *
 * @link http://www.pogostick.com Pogostick, LLC.
 * @license http://www.pogostick.com/licensing
 * @author Jerry Ablan <yiixl@pogostick.com>
 *
 * @since v1.0.0
 *
 * @package yiixl
 * @subpackage core.components
 *
 * @filesource
 */
/**
 * xlRestAction represents a REST action that is defined as a xlRestController method.
 * The method name is like '<method>XYZ' where <method> is the type of HTTP request and
 * 'XYZ' is the action name.
 */
class xlRestAction extends CAction implements xlIComponent
{
	//********************************************************************************
	//* Constants
	//********************************************************************************

	/**
	 * Our logging tag
	 */
	const	CLASS_LOG_TAG = 'yiixl.core.components.xlRestAction';

	//********************************************************************************
	//* Public Methods
	//********************************************************************************
	
	/**
	* Runs the REST action.
	* @throws CHttpException
	*/
	public function run()
	{
		$_controller = $this->getController();
		
		if ( ! ( $_controller instanceof IXLRest ) )
		{
			$_controller->missingAction( $this->getId() );
			return;
		}
		
		//	Call the controllers dispatch method...
		$_controller->dispatchRequest( $this );
	}

}