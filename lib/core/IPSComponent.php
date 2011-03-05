<?php
/**
 * This file is part of the psYiiExtensions package.
 *
 * @copyright Copyright &copy; 2009-2010 Pogostick, LLC
 * @link http://www.pogostick.com Pogostick, LLC.
 * @license http://www.pogostick.com/licensing
 * @filesource
 */

//	All components require these
Yii::import( 'pogostick.helpers.PS', true );
Yii::import( 'pogostick.base.CPSException', true );

/**
 * This interface identifies and object as a YiiXL component
 *
 * @package		psYiiExtensions
 * @subpackage 	base.interfaces
 *
 * @author			Jerry Ablan <jablan@pogostick.com>
 * @version		SVN $Id: IPSComponent.php 358 2010-01-02 23:33:40Z jerryablan@gmail.com $
 * @since			v1.0.6
 */
interface IPSComponent extends IPSBase
{
}