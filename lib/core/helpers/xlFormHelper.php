<?php
/**
 * YiiXL(tm) : The Yii Extension Library of Doom! (http://github.com/Pogostick/yiixl/)
 * Copyright 2009-2011, Pogostick, LLC. (http://www.pogostick.com/)
 *
 * Dual licensed under the MIT License and the GNU General Public License (GPL) Version 2.
 * See {@link http://www.pogostick.com/licensing/} for complete information.
 *
 * @copyright		Copyright 2009-2011, Pogostick, LLC. (http://www.pogostick.com/)
 * @link			https://github.com/Pogostick/yiixl/ The Yii Extension Library of Doom!
 * @license			http://www.pogostick.com/licensing
 * @author			Jerry Ablan <yiixl@pogostick.com>
 *
 * @package			yiixl.core.helpers
 * @category		Helpers
 * @since			v1.0.0
 *
 * @filesource
 */
/**
 * xlFormHelper
 * Various form helper methods
 */
class xlFormHelper
{
	//*************************************************************************
	//* Class Constants 
	//*************************************************************************

	//*************************************************************************
	//* Private Members 
	//*************************************************************************

	//*************************************************************************
	//* Public Methods 
	//*************************************************************************

	//*************************************************************************
	//* Private Methods 
	//*************************************************************************

	//*************************************************************************
	//* Properties
	//*************************************************************************

	/**
	 * @param xlModel $model
	 * @param bool $addHeader
	 * @param bool $returnOutput
	 * @return string
	 */
	public function advancedSearch( $model, $addHeader = true, $returnOutput = false )
	{
		$_header = ( $addHeader ? '<?php' : null );
		$_searchFields = null;

		$_criteria = $model->search()->getCriteria();

		foreach ( $model->getTableSchema()->getColumnNames() as $_columnName )
		{
			if ( false !== ( stripos( $_criteria->condition, $_columnName ) ) )
				$_searchFields .= '$_fieldList[] = array( XL::TEXT, \'' . $_columnName . '\' );' . PHP_EOL;
		}

		$_output = <<<HTML
{$_header}
/**
 * Advanced Search Partial Form
 */

xlFormHelper::setShowRequiredLabel( false );

\$_formOptions = array(
	'formModel' => \$model,
	'formClass' => 'wide form ui-widget',
	'action' => XL::_cu( \$model->route ),
	'method' => 'GET',
	'showLegend' => false,
	'uiStyle' => XL::UI_JQUERY,
);

\$_fieldList = array();

\$_fieldList[] = array( 'html', '<fieldset><legend>Advanced Search</legend>' );
{$_searchFields}
\$_fieldList[] = array( 'html', XL::submitButton( 'Search', array( 'style' => 'float:right;margin-top:5px;' ) ) );
\$_fieldList[] = array( 'html', '</fieldset>' );

\$_formOptions['fields'] = \$_fieldList;

xlFormHelper::create( \$_formOptions );

HTML;

		if ( $returnOutput )
			return $_output;

		echo $_output;
	}

}
