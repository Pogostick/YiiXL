<?php
/**
 * This file is part of the YiiXL package.
 *
 * @copyright Copyright (c) 2009-2011 Pogostick, LLC.
 * @link http://www.pogostick.com Pogostick, LLC.
 * @license http://www.pogostick.com/licensing
 * @author Jerry Ablan <jablan@pogostick.com>
 *
 * @since v1.0.0
 *
 * @package yiixl
 * @subpackage core.components
 *
 * @filesource
 */

/**
 * CXLRestAction represents a REST action that is defined as a CXLRestController method.
 * The method name is like '<method>XYZ' where <method> is the type of HTTP request and
 * 'XYZ' is the action name.
 */
class CXLRestAction extends CAction implements IXLComponent
{
	//********************************************************************************
	//* Constants
	//********************************************************************************

	/**
	 * Our logging tag
	 */
	const	CLASS_LOG_TAG = 'yiixl.core.components.CXLRestAction';

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