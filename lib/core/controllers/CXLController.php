<?php
/**
 * This file is part of the YiiXL package.
 *
 * @copyright Copyright (c) 2009-2011 Pogostick, LLC.
 * @link http://www.pogostick.com Pogostick, LLC.
 * @license http://www.pogostick.com/licensing
 * @package yiixl
 * @subpackage core.controllers
 * @author 		Jerry Ablan <jablan@pogostick.com>
 * @filesource
 */

/**
 * CXLController
 * Provides filtered access to resources
 */
abstract class CXLController extends CController implements IXLComponent, IXLAccessControl
{
	//********************************************************************************
	//* Constants
	//********************************************************************************

	/**
	* @var integer The number of items to display per page
	*/
	const PAGE_SIZE = 10;

	/**
	 * The name of our command form field
	 */
	const COMMAND_FIELD_NAME = '__psCommand';

	/**
	 * Standard search text for rendering
	 */
	const SEARCH_HELP_TEXT = 'You may optionally enter a comparison operator (<b>&lt;</b>, <b>&lt;=</b>, <b>&gt;</b>, <b>&gt;=</b>, <b>&lt;&gt;</b>or <b>=</b>) at the beginning of each search value to specify how the comparison should be done.';

	//********************************************************************************
	//* Member Variables
	//********************************************************************************

	/**
	 * @var array context menu items. This property will be assigned to {@link CMenu::items}.
	 */
	protected $_menu = array();
	public function getMenu() { return $this->_menu; }
	public function setMenu( $value ) { $this->_menu = $value; return $this; }

	/**
	 * @var array the breadcrumbs of the current page. The value of this property will
	 * be assigned to {@link CBreadcrumbs::links}. Please refer to {@link CBreadcrumbs::links}
	 * for more details on how to specify this property.
	 */
	protected $_breadcrumbs = array();
	public function getBreadcrumbs() { return $this->_breadcrumbs; }
	public function setBreadcrumbs( $value ) { $this->_breadcrumbs = $value; return $this; }

	/**
	 * An optional, additional page heading
	 * @var string
	 */
	protected $_pageHeading;
	public function getPageHeading() { return $this->m_sPageHeading; }
	public function setPageHeading( $value ) { $this->m_sPageHeading = $value; return $this; }

	/***
	* @var string Allows you to change your action prefix
	*/
	protected $_actionMethodPrefix = 'action';
	public function getActionMethodPrefix() { return $this->_actionMethodPrefix; }
	public function setActionMethodPrefix( $value ) { $this->_actionMethodPrefix = $value; return $this; }

	/**
	* @var CActiveRecord The currently loaded data model instance.
	* @access protected
	*/
	protected $_currentModel = null;
	public function getCurrentModel() { return $this->_currentModel; }
	protected function setCurrentModel( $value ) { $this->_currentModel = $value; return $this; }

	/**
	* @var string The name of the model for this controller
	* @access protected
	*/
	protected $_modelClass = null;
	public function getModelClass() { return $this->_modelClass; }
	protected function setModelClass( $value ) { $this->_modelClass = $value; return $this; }

	/**
	* Convenience access to isPostRequest
	* @return boolean
	*/
	public function getIsPostRequest() { return PS::isPostRequest(); }

	/**
	* Convenience access to isAjaxRequest
	* @return boolean
	*/
	public function getIsAjaxRequest() { return PS::isAjaxRequest(); }

	/**
	 * Returns the base url of the current app
	 * @return string
	 */
	public function getAppBaseUrl() { return PS::_gbu(); }

	/**
	 * @var string the layout of the content portion of this controller. If specified,
	 * content is passed through this layout before it is sent to your main page layout.
	 */
	protected $_contentTemplate = null;
	public function getContentTemplate() { return $this->_contentTemplate; }
	public function setContentTemplate( $value ) { $this->_contentTemplate = $value; return $this; }

	/**
	* @var boolean Tries to find proper layout to use based on name
	* @access protected
	*/
	protected $_autoFindLayout = true;
	public function getAutoFindLayout() { return $this->_autoFindLayout; }
	public function setAutoFindLayout( $value ) { $this->_autoFindLayout = $value; return $this; }

	/**
	* @var boolean Try to find missing action
	* @access protected
	*/
	protected $_autoFindAction = true;
	public function getAutoFindAction() { return $this->_autoFindAction; }
	public function setAutoFindAction( $value ) { $this->_autoFindAction = $value; }

	/**
	* @var array An associative array of POST commands and their applicable methods
	* @access protected
	*/
	protected $_adminCommandMap = array();
	public function getAdminCommandMap() { return $this->_adminCommandMap; }
	public function setAdminCommandMap( $value ) { $this->_adminCommandMap = $value; return $this; }


	public function addCommandToMap( $key, $value = null, $property = null ) { $this->_adminCommandMap[ $key ] = $value; if ( $property ) $this->addActionControls( $property, array( $key ) ); return $this; }

	/**
	* Action queue for keeping track of where we are...
	* @var array
	*/
	protected $_actionStack = array();

	/**
	 * A list of actions registered by our portlets
	 * @var array
	 */
	protected $_portletActions = array();
	public function getPortletActions() { return $this->_portletActions; }
	public function setPortletActions( $value ) { $this->_portletActions = $value; return $this; }
	public function addPortletAction( $sName, $arCallback ) { $this->_portletActions[ $sName ] = $arCallback; return $this; }

	/**
	 * @var array $viewData The array of data passed to views
	 */
	protected $_viewData = array();
	protected function getViewData() { return $this->_viewData; }
	protected function setViewData( $value ) { $this->_viewData = $value; return $this; }

	/**
	 * @var array Any values in this array will be extracted into each view before it's rendered. The value "currentUser" is added automatically.
	 */
	protected $_extraViewDataList = array();
	protected function getExtraViewDataList() { return $this->_extraViewDataList; }
	protected function setExtraViewDataList( $value ) { $this->_extraViewDataList = $value; return $this; }

	/**
	 * @var string The prefix to prepend to variables extracted into the view from {@link $_extraViewDataList}. Defaults to '_' (single underscore).
	 */
	protected $_extraViewDataPrefix = '_';
	protected function getExtraViewDataPrefix() { return $this->_extraViewDataPrefix; }
	protected function setExtraViewDataPrefix( $value ) { $this->_extraViewDataPrefix = $value; return $this; }

	//********************************************************************************
	//* Public Methods
	//********************************************************************************

	/**
	* Initialize the controller
	*
	*/
	public function init()
	{
		//	Phone home...
		parent::init();

		//	Find layout...
		if ( PHP_SAPI != 'cli' && $this->_autoFindLayout && ! Yii::app() instanceof CConsoleApplication )
		{
			if ( file_exists( PS::_gbp() . '/views/layouts/' . $this->getId() . '.php' ) )
				$this->_pageLayout = $this->getId();
		}

		//	Allow errors
		$this->addActionControl( self::ACCESS_TO_ANY, 'error' );

		//	Ensure conformity
		if ( ! is_array( $this->_extraViewDataList ) )
			$this->_extraViewDataList = array();

		//	Add "currentUser" value to extra view data
		if ( null === PS::o( $this->_extraViewDataList, $this->_extraViewDataPrefix . 'currentUser' ) )
			$this->_extraViewDataList[ $this->_extraViewDataPrefix . 'currentUser' ] = PS::_gcu();

		//	And some defaults...
		$this->defaultAction = 'index';
	}

	/**
	 * How about a default action that displays static pages? Huh? Huh?
	 *
	 * In your configuration file, configure the urlManager as follows:
	 *
	 *	'urlManager' => array(
	 *		'urlFormat' => 'path',
	 *		'showScriptName' => false,
	 *		'rules' => array(
	 *			... all your rules should be first ...
	 *			//	Add this as the last line in your rules.
	 *			'<view:\w+>' => 'default/_static',
	 *		),
	 *
	 * The above assumes your default controller is DefaultController. If is different
	 * simply change the route above (default/_static) to your default route.
	 *
	 * Finally, create a directory under your default controller's view path:
	 *
	 *		/path/to/your/app/protected/views/default/_static
	 *
	 * Place your static files in there, for example:
	 *
	 *		/path/to/your/app/protected/views/default/_static/aboutUs.php
	 *		/path/to/your/app/protected/views/default/_static/contactUs.php
	 *		/path/to/your/app/protected/views/default/_static/help.php
	 *
	 * @return array
	 */
	public function actions()
	{
		return array_merge(
			array(
				'_static' => array(
					'class' => 'CViewAction',
					'basePath' => '_static',
				),
			),
			parent::actions()
		);
	}

	/**
	* A generic action that renders a page and passes in the model
	*
	* @param string The action id
	* @param CModel The model
	* @param array Extra parameters to pass to the view
	* @param string The name of the variable to pass to the view. Defaults to 'model'
	*/
	public function genericAction( $actionId, $oModel = null, $arExtraParams = array(), $sModelVarName = 'model', $sFlashKey = null, $sFlashValue = null, $sFlashDefaultValue = null )
	{
		if ( $sFlashKey ) Yii::app()->user->setFlash( $sFlashKey, $sFlashValue, $sFlashDefaultValue );
		$this->render( $actionId, array_merge( $arExtraParams, array( $sModelVarName => ( $oModel ) ? $oModel : $this->loadCurrentModel() ) ) );
	}

	/**
	* Returns the data model based on the primary key given in the GET variable.
	* If the data model is not found, an HTTP exception will be raised.
	*
	* @param integer the primary key value. Defaults to null, meaning using the 'id' GET variable
	* @throws CHttpException
	*/
	public function loadCurrentModel( $iId = null )
	{
		if ( null === $this->_currentModel )
		{
			$_iId = PS::o( $_GET, 'id', $iId );
			$this->_currentModel = $this->load( $_iId );

			//	No data? bug out
			if ( null === $this->_currentModel ) $this->redirect( array( $this->defaultAction ) );

			//	Get the name of this model...
			$this->setModelClass( get_class( $this->_currentModel ) );
		}

		//	Return our model...
		return $this->_currentModel;
	}

	/**
	* Provide automatic missing action mapping...
	* Also handles a theme change request from any portlets
	*
	* @param string $actionId
	*/
	public function missingAction( $actionId = null )
	{
		if ( $this->_autoFindAction )
		{
			if ( empty( $actionId ) ) $actionId = $this->defaultAction;

			if ( $this->getViewFile( $actionId ) )
			{
				$this->render( $actionId );
				return;
			}
		}

		parent::missingAction( $actionId );
	}

	/**
	* Our error handler...
	*
	*/
	public function actionError()
	{
		if ( ! $_arError = Yii::app()->errorHandler->error )
		{
			if ( $this->isAjaxRequest )
				echo $_arError['message'];
			else
				throw new CHttpException( 404, 'Page not found.' );
		}

		$this->render( 'error', array( 'error' => $_arError ) );
	}

	/**
	* Convenience access to Yii request
	*
	*/
	public function getRequest()
	{
		return Yii::app()->getRequest();
	}

	/**
	 * See if there are any commands that need processing
	 * @param CAction $oAction
	 * @return boolean
	 */
	public function beforeAction( $oAction )
	{
		//	If we have commands, give it a shot...
		if ( count( $this->_adminCommandMap ) && parent::beforeAction( $oAction ) )
			$this->processCommand();

		return true;
	}

	/**
	 * Renders a view with a layout.
	 *
	 * This method first calls {@link renderPartial} to render the view (called content view).
	 * It then renders the layout view which may embed the content view at appropriate place.
	 * In the layout view, the content view rendering result can be accessed via variable
	 * <code>$content</code>. At the end, it calls {@link processOutput} to insert scripts
	 * and dynamic contents if they are available.
	 *
	 * By default, the layout view script is "protected/views/layouts/main.php".
	 * This may be customized by changing {@link layout}.
	 *
	 * @param string name of the view to be rendered. See {@link getViewFile} for details
	 * about how the view script is resolved.
	 * @param array data to be extracted into PHP variables and made available to the view script
	 * @param boolean whether the rendering result should be returned instead of being displayed to end users.
	 * @return string the rendering result. Null if the rendering result is not required.
	 * @see renderPartial
	 * @see getLayoutFile
	 */
	public function newRender( $viewName, $viewData = null, $returnString = false )
	{
		//	make sure we're all on the same page...
		$this->_pageLayout = $this->layout;

		$_output = $this->renderPartial( $viewName, $viewData, true );

		if ( $this->_pageLayout && false !== ( $_layoutFile = $this->getLayoutFile( $this->_pageLayout ) ) )
		{
			//	Process content layout if required
			if ( $this->_contentTemplate && false !== ( $_contentTemplateFile = $this->getLayoutFile( $this->_contentTemplate ) ) )
				$_output = $this->renderPartial( $_contentTemplateFile, $viewData, true );

			$_output = $this->renderFile( $_layoutFile, array( 'content' => $_output ), true );
			$_output = $this->processOutput( $_output );
		}

		if ( $returnString )
			return $_output;

		echo $_output;
	}

	/**
	 * Renders a view.
	 *
	 * The named view refers to a PHP script (resolved via {@link getViewFile})
	 * that is included by this method. If $data is an associative array,
	 * it will be extracted as PHP variables and made available to the script.
	 *
	 * This method differs from {@link render()} in that it does not
	 * apply a layout to the rendered result. It is thus mostly used
	 * in rendering a partial view, or an AJAX response.
	 *
	 * This override adds the current user to the data automatically in the $_currentUser variable
	 *
	 * @param string name of the view to be rendered. See {@link getViewFile} for details
	 * about how the view script is resolved.
	 * @param array data to be extracted into PHP variables and made available to the view script
	 * @param boolean whether the rendering result should be returned instead of being displayed to end users
	 * @param boolean whether the rendering result should be postprocessed using {@link processOutput}.
	 * @return string the rendering result. Null if the rendering result is not required.
	 * @throws CException if the view does not exist
	 * @see getViewFile
	 * @see processOutput
	 * @see render
	 */
	public function renderPartial( $view, $data = null, $return = false, $processOutput = false )
	{
		if ( false === ( $_viewFile = $this->getViewFile( $view ) ) )
		{
			throw new CException(
				Yii::t(
					'yii',
					'{controller} cannot find the requested view "{view}".',
					array(
						'{controller}' => get_class( $this ),
						'{view}' => $view
					)
				)
			);
		}

		if ( null === $data )
			$data = array();

		$_output = $this->renderFile(
			$_viewFile,
			array_merge(
				$this->_extraViewDataList,
				$this->_viewData,
				$data
			),
			true
		);

		if ( $processOutput )
			$_output = $this->processOutput( $_output );

		if ( $return )
			return $_output;

		echo $_output;
	}

	/**
	 * Creates a standard form options array and loads page niceties
	 * @param CModel $model
	 * @param string|array If a string is passed in, it is used as the title.
	 * @return array
	 */
	public function setStandardFormOptions( $model, $optionList = array() )
	{
		//	Shortcut... only passed in the title...
		if ( is_string( $optionList ) )
		{
			$_title = $optionList;

			$optionList = array(
				'title' => PS::_gan() . ' :: ' . $_title,
				'breadcrumbs' => array( $_title ),
			);
		}

		//	Abbreviated arguments?
		if ( is_array( $model ) && array() === $optionList )
		{
			$optionList = $model;
			$model = PS::o( $optionList, 'model' );
		}

		//	Set the standard nav options
		$this->setViewNavigationOptions( $optionList );

		$_formId = PS::o( $optionList, 'id', 'ps-edit-form' );

		//	Put a cool flash span on the page
		if ( PS::o( $options, 'enableFlash', true, true ) )
		{
			$_flashClass = PS::o( $options, 'flashSuccessClass', 'operation-result-success' );

			if ( null === ( $_message = PS::_gf( 'success' ) ) )
			{
				if ( null !== ( $_message = PS::_gf( 'failure' ) ) )
					$_flashClass = PS::o( $options, 'flashFailureClass', 'operation-result-failure' );
			}

			$_spanId = PS::o( $options, 'flashSpanId', 'operation-result', true );
			PS::_ss( 'psForm-flash-html', PS::tag( 'span', array( 'id' => $_spanId, 'class' => $_flashClass ), $_message ) );

			//	Register a nice little fader...
			$_fader =<<<SCRIPT
$('#{$_spanId}').fadeIn('500',function(){
	$(this).delay(3000).fadeOut(3500);
});
SCRIPT;

			PS::_rs( $_formId . '.' . $_spanId . '.fader', $_fader, CClientScript::POS_READY );
		}

		PS::setFormFieldContainerClass( PS::o( $optionList, 'rowClass', 'row' ) );

		$_formOptions = array(
			'id' => $_formId,
			'showLegend' => PS::o( $optionList, 'showLegend', true ),
			'showDates' => PS::o( $optionList, 'showDates', false ),
			'method' => PS::o( $optionList, 'method', 'POST' ),

			'uiStyle' => PS::o( $optionList, 'uiStyle', PS::UI_JQUERY ),
			'formClass' => PS::o( $optionList, 'formClass', 'form' ),
			'formModel' => $model,
			'errorCss' => PS::o( $optionList, 'errorCss', 'error' ),

			//	We want error summary...
			'errorSummary' => PS::o( $optionList, 'errorSummary', true ),
			'errorSummaryOptions' => array(
				'header' => '<p>The following problems occurred:</p>',
			),

			'validate' => PS::o( $optionList, 'validate', true ),

			'validateOptions' => PS::o( $optionList, 'validateOptions',
				array(
					'ignoreTitle' => true,
					'errorClass' => 'ps-validate-error',
				)
			),
		);

		//	Do some auto-page-setup...
		if ( null !== ( $_header = PS::o( $optionList, 'header', PS::o( $optionList, 'title' ) ) ) )
		{
			if ( null !== ( $_headerIcon = PS::o( $optionList, 'headerIcon' ) ) )
				$_header = PS::tag( 'span', array(), PS::image( $_headerIcon ) ) . $_header;

			echo PS::tag( 'h1', array( 'class' => 'ui-generated-header' ), $_header );
		}

		//	Do some auto-page-setup...
		if ( null !== ( $_subHeader = PS::o( $optionList, 'subHeader' ) ) )
			echo PS::tag( 'div', array(), $_subHeader );

		if ( false !== PS::o( $optionList, 'renderSearch', false ) )
		{
			echo PS::tag( 'p', array(), self::SEARCH_HELP_TEXT );
			echo PS::link( 'Advanced Search', '#', array( 'class' => 'search-button' ) );

			echo PS::tag(
				'div',
				array( 'class' => 'search-form' ),
				$this->renderPartial( '_search', array( 'model' => $model ), true )
			);

			//	Register the search script, if any
			if ( null !== ( $_searchScript = PS::o( $optionList, '__searchScript' ) ) )
				PS::_rs( 'search', $_searchScript );
		}

		if ( PS::UI_JQUERY == ( $_uiStyle = PS::o( $optionList, 'uiStyle', PS::UI_JQUERY ) ) )
			CPSjqUIWrapper::loadScripts();

		return $_formOptions;
	}

	/**
	 * Sets the content type for this page to the specified MIME type
	 * @param <type> $contentType The MIME type to set
	 * @param boolean $noLayout If true, the layout for this page is set to false
	 */
	public function setContentType( $contentType, $noLayout = true )
	{
		if ( $noLayout ) $this->layout = false;
		header( 'Content-Type: ' . $contentType );
	}

	/**
	 * Sets the standard page navigation options (title, crumbs, menu, etc.)
	 * @param array $options
	 */
	public function setViewNavigationOptions( &$options = array() )
	{
		//	Page title
		$_title = PS::o( $options, 'title', null, true );
		$_subtitle = PS::o( $options, 'subtitle', null, true );
		$_header = PS::o( $options, 'header' );

		//	Generate subtitle from header...
		if ( null === $_title && null === $_subtitle && null !== $_header )
			$_subtitle = $_header;

		if ( $_subtitle )
			$_title = PS::_gan() . ' :: ' . $_subtitle;

		if ( ! $_title )
			$_title =  PS::_gan();

		$this->setPageTitle( $options['title'] = $_title );

		//	Set crumbs
		$this->_breadcrumbs = PS::o( $options, 'breadcrumbs' );

		//	Let side menu be set from here as well...
		if ( null !== ( $_menuItems = PS::o( $options, 'menu', null ) ) )
		{
			//	Rebuild menu items if not in standard format
			$_finalMenu = array();

			foreach ( $_menuItems as $_itemLabel => $_itemLink )
			{
				if ( null === ( $_label = PS::o( $_itemLink, 'label', null, true ) ) )
					$_label = $_itemLabel;

				if ( null === ( $_link = PS::o( $_itemLink, 'link', null, true ) ) )
					$_link = $_itemLink;

				$_finalMenu[] = array(
					'label' => $_label,
					'url' => $_link,
				);
			}

			$options['menu'] = $this->_menu = $_finalMenu;
		}

		$_enableSearch = ( PS::o( $options, 'enableSearch', false ) || PS::o( $options, 'renderSearch', false ) );

		//	Drop the search script on the page if enabled...
		if ( false !== $_enableSearch )
		{
			$_searchSelector = PS::o( $options, 'searchSelector', '.search-button' );
			$_toggleSpeed = PS::o( $options, 'toggleSpeed', 'fast' );
			$_searchForm = PS::o( $options, 'searchForm', '.search-form' );
			$_targetFormId = PS::o( $options, 'id', 'ps-edit-form' );

			$_searchScript =<<<JS
$(function(){
	$('{$_searchSelector}').click(function(){
		$('{$_searchForm}').slideToggle('{$_toggleSpeed}');
		return false;
	});

	$('{$_searchForm} form').submit(function(){
		$.fn.yiiGridView.update('{$_targetFormId}', {
			data: $(this).serialize()
		});
		return false;
	});
});
JS;
			$options['__searchScript'] = $_searchScript;
		}

		//	Return reconstructed options for standard form use
		return $options;
	}

	//********************************************************************************
	//* Private Methods
	//********************************************************************************

	/**
	* Executes any commands
	* Maps to {@link CXLController::commandMap} and calls the appropriate method.
	*
	* @return mixed
	*/
	protected function processCommand( $arData = array(), $sIndexName = self::COMMAND_FIELD_NAME )
	{
		//	Our return variable
		$_oResults = null;

		//	Get command's method...
		$_sCmd = PS::o( $_REQUEST, $sIndexName );

		//	Do we have a command mapping?
		if ( null !== ( $_arCmd = PS::o( $this->_adminCommandMap, $_sCmd ) ) )
		{
			//	Get any miscellaneous data into the appropriate array
			if ( count( $arData ) )
			{
				if ( $this->getIsPostRequest() )
					$_POST = array_merge( $_POST, $arData );
				else
					$_GET = array_merge( $_GET, $arData );
			}

			$_oResults = call_user_func( $_arCmd[1] );
		}

		//	Return the results
		return $_oResults;
	}

	/**
	* Saves the data in the model
	*
	* @param CModel $oModel The model to save
	* @param array $arData The array of data to merge with the model
	* @param string $sRedirectAction Where to redirect after a successful save
	* @param boolean $bAttributesSet If true, attributes will not be set from $arData
	* @param string $sModelName Optional model name
	* @param string $sSuccessMessage Flash message to set if successful
	* @param boolean $bNoCommit If true, transaction will not be committed
	* @return boolean
	*/
	protected function saveCurrentModel( &$oModel, $arData = array(), $sRedirectAction = 'show', $bAttributesSet = false, $sModelName = null, $sSuccessMessage = null, $bNoCommit = false, $bSafeOnly = false )
	{
		$_sMessage = PS::nvl( $sSuccessMessage, 'Your changes have been saved.' );
		$_sModelName = PS::nvl( $sModelName, PS::nvl( $oModel->getModelClass(), $this->_modelClass ) );

		if ( isset( $arData, $arData[ $_sModelName ] ) )
		{
			if ( ! $bAttributesSet ) $oModel->setAttributes( $arData[ $_sModelName ], $bSafeOnly );

			if ( $oModel->save() )
			{
				if ( ! $bNoCommit && $oModel instanceof CPSModel && $oModel->hasTransaction() ) $oModel->commitTransaction();

				Yii::app()->user->setFlash( 'success', $_sMessage );

				if ( $sRedirectAction )
					$this->redirect( array( $sRedirectAction, 'id' => $oModel->id ) );

				return true;
			}
		}

		return false;
	}

	/***
	* Just like saveModel, but doesn't commit, and never redirects.
	*
	* @param CPSModel $oModel
	* @param array $arData
	* @param boolean $bAttributesSet
	* @param string $sSuccessMessage
	* @return boolean
	* @see saveModel
	*/
	protected function saveTransactionCurrentModel( &$oModel, $arData = array(), $bAttributesSet = false, $sSuccessMessage = null )
	{
		return $this->saveCurrentModel( $oModel, $arData, false, $bAttributesSet, null, $sSuccessMessage, true );
	}

	/**
	* Loads a page of models
	* @param boolean Whether or not to apply a sort. Defaults to false
	*
	* @return array Element 0 is the results of the find. Element 1 is the pagination object
	*/
	protected function loadPaged( $bSort = false, $oCriteria = null )
	{
		$_oSort = $_oCrit = $_oPage = null;

		//	Make criteria
		$_oCrit = PS::nvl( $oCriteria, new CDbCriteria() );
		$_oPage = new CPagination( $this->loadCount( $_oCrit ) );
		$_oPage->pageSize = PS::o( $_REQUEST, 'perPage', self::PAGE_SIZE );
		if ( isset( $_REQUEST, $_REQUEST['page'] ) ) $_oPage->setCurrentPage( intval( $_REQUEST['page'] ) - 1 );
		$_oPage->applyLimit( $_oCrit );

		//	Sort...
		if ( $bSort )
		{
			$_oSort = new CPSSort( $this->_modelClass );
			$_oSort->applyOrder( $_oCrit );
		}

		//	Return an array of what we've build...
		return array( $this->loadAll( $_oCrit ), $_oCrit, $_oPage, $_oSort );
	}

	/**
	* Loads a model(s) based on criteria and scopes.
	*
	* @param string The method to append
	* @param CDbCriteria The criteria for the lookup
	* @param array Scopes to apply to this request
	* @param array Options for the data load
	* @return CActiveRecord|array
	*/
	protected function genericModelLoad( $sMethod, &$oCrit = null, $arScope = array(), $arOptions = array() )
	{
		$_sMethod = $this->getModelLoadString( $arScope, $arOptions ) . $sMethod;
		return eval( "return (" . $_sMethod . ");" );
	}

	/**
	* This method reads the data from the database and returns the row.
	* Must override in subclasses.
	* @var integer $iId The primary key to look up
	* @return CActiveRecord
	*/
	protected function load( $iId = null )
	{
		return $this->genericModelLoad( 'findByPk(' . $iId . ')' );
	}

	/**
	* Loads all data using supplied criteria
	* @param CDbCriteria $oCrit
	* @return array Array of CActiveRecord
	* @todo When using PHP v5.3, {@link eval} will no longer be needed
	*/
	protected function loadAll( &$oCrit = null )
	{
		return $this->genericModelLoad( 'findAll(' . ( null !== $oCrit ? '$oCrit' : '' ) . ')', $oCrit );
	}

	/**
	* Returns the count of rows that match the supplied criteria
	*
	* @param CDbCriteria $oCrit
	* @return integer The number of rows
	*/
	protected function loadCount( &$oCrit = null )
	{
		$_sCrit = ( $oCrit ) ? '$oCrit' : null;
		return $this->genericModelLoad( 'count(' . $_sCrit. ')', $oCrit );
	}

	/**
	* Builds a string suitable for {@link eval}. The verb is intentionally not appeneded.
	*
	* @param array $arScope
	* @return string
	* @todo Will be deprecated after upgrade to PHP v5.3
	*/
	protected function getModelLoadString( $arScope = array(), $arOptions = array() )
	{
		$_sScopes = ( count( $arScope ) ) ? implode( '->', $arScope ) . '->' : null;
		return $this->_modelClass . '::model()->' . $_sScopes;
	}

	/**
	* Pushes an action onto the action queue
	*
	* @param CAction $oAction
	*/
	protected function pushAction( $oAction )
	{
		array_push( $this->_actionStack, $oAction );
	}

	/**
	* Retrieves the latest pushed action
	* @return CAction
	*/
	protected function popAction()
	{
		return array_pop( $this->_actionStack );
	}

	/**
	* Pushes a variable onto the view data stack
	*
	* @param string $variableName
	* @param mixed $variableData
	*/
	protected function addViewData( $variableName, $variableData = null )
	{
		$this->_viewData[$variableName] = $variableData;
	}

	/**
	* Clears the current search criteria
	* @return null
	*/
	protected function clearSearchCriteria()
	{
		$this->m_arCurrentSearchCriteria = null;
		Yii::app()->user->clearState( $this->m_sSearchStateId );

		return null;
	}

	/**
	 * Turns off the layout, echos the JSON encoded version of data and returns. Optionally encoding HTML characters.
	 * @param array $payload The response data
	 * @param boolean $encode If true, response is run through htmlspecialchars()
	 * @param integer $encodeOptions Options for htmlspecialchars. Defaults to ENT_NOQUOTES
	 */
	protected function _ajaxReturn( $payload = false, $encode = false, $encodeOptions = ENT_NOQUOTES )
	{
		$this->layout = false;

		if ( false === $payload || true === $payload )
			$payload = ( $payload ? '1' : '0' );

		$_result = json_encode( $payload );
		if ( $encode ) $_result = htmlspecialchars( $_result, $encodeOptions );

		echo $_result;
		return;
	}

}