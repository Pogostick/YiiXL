<?php
/**
 * This file is part of YiiXL
 * Copyright (c) 2009-2011, Pogostick, LLC. All rights reserved.
 *
 * @link http://www.pogostick.com Pogostick, LLC.
 * @license http://www.pogostick.com/licensing
 * @author Jerry Ablan <jablan@pogostick.com>
 *
 * @since v1.0.0
 *
 * @package yiixl
 * @subpackage core.controllers
 *
 * @filesource
 */
/**
 * CXLController
 * Provides filtered access to resources
 */
abstract class CXLController extends CController implements IXLController, IXLAccessControl
{
	//********************************************************************************
	//* Constants
	//********************************************************************************

	/**
	 * Our logging tag
	 * @constant string
	 */
	const CLASS_LOG_TAG = 'yiixl.core.components.CXLController';

	 //********************************************************************************
	//* Member Variables
	//********************************************************************************

	/**
	 * Context menu items. This property will be assigned to {@link CMenu::items}.
	 * @var array 
	 */
	protected $_menu = array( );
	/**
	 * The breadcrumbs of the current page. The value of this property will
	 * be assigned to {@link CBreadcrumbs::links}. Please refer to 
	 * {@link CBreadcrumbs::links} for more details on how to specify this property.
	 * 
	 * @var array 
	 */
	protected $_breadcrumbs = array( );
	/**
	 * An optional, additional page heading
	 * 
	 * @var string
	 */
	protected $_pageHeading;
	/**
	 * Allows you to change your action prefix
	 * @var string 
	 */
	protected $_actionMethodPrefix = 'action';
	/**
	 * @var CActiveRecord The currently loaded data model instance.
	 * @access protected
	 */
	protected $_currentModel = null;
	/**
	 * The name of the model for this controller
	 * @var string 
	 * @access protected
	 */
	protected $_modelClass = null;
	/**
	 * The layout of the content portion of this controller. If specified,
	 * content is passed through this layout before it is sent to your main page layout.
	 * @var string 
	 */
	protected $_contentTemplate = null;
	/**
	 * Tries to find proper layout to use based on name
	 * @var boolean 
	 * @access protected
	 */
	protected $_autoFindLayout = true;
	/**
	 * Try to find missing action
	 * @var boolean 
	 * @access protected
	 */
	protected $_autoFindAction = true;
	/**
	 * An associative array of POST commands and their applicable methods
	 * @var array 
	 * @access protected
	 */
	protected $_adminCommandMap = array( );
	/**
	 * Action queue for keeping track of where we are...
	 * @var array
	 */
	protected $_actionStack = array( );
	/**
	 * A list of actions registered by our portlets
	 * @var array
	 */
	protected $_portletActions = array( );
	/**
	 * The array of data passed to views
	 * @var array 
	 */
	protected $_viewData = array( );
	/**
	 * Any values in this array will be extracted into each view before 
	 * it's rendered. The value "currentUser" is added automatically.
	 * @var array 
	 */
	protected $_extraViewDataList = array( );
	/**
	 * The prefix to prepend to variables extracted into the view from 
	 * {@link $_extraViewDataList}. Defaults to '_' (single underscore).
	 * @var string 
	 */
	protected $_extraViewDataPrefix = '_';

	//********************************************************************************
	//* Public Methods
	//********************************************************************************

	/**
	 * Initialize the controller
	 */
	public function init()
	{
		//	Phone home...
		parent::init();

		//	Find layout...
		if ( PHP_SAPI != 'cli' && $this->_autoFindLayout && ! XL::_a() instanceof CConsoleApplication )
		{
			if ( file_exists( XL::_gbp() . '/views/layouts/' . $this->getId() . '.php' ) ) 
				$this->_pageLayout = $this->getId();
		}

		//	Allow errors
		$this->addActionControl( self::ACCESS_TO_ANY, 'error' );

		//	Ensure conformity
		if ( !is_array( $this->_extraViewDataList ) ) 
			$this->_extraViewDataList = array();

		//	Add "currentUser" value to extra view data
		if ( null === XL::o( $this->_extraViewDataList, $this->_extraViewDataPrefix . 'currentUser' ) ) 
			$this->_extraViewDataList[$this->_extraViewDataPrefix . 'currentUser'] = XL::_gcu();

		//	And some defaults...
		$this->defaultAction = 'index';
	}

	/**
	 * How about a default action that displays static pages? Huh? Huh?
	 *
	 * In your configuration file, configure the urlManager as follows:
	 *
	 * 	'urlManager' => array(
	 * 		'urlFormat' => 'path',
	 * 		'showScriptName' => false,
	 * 		'rules' => array(
	 * 			... all your rules should be first ...
	 * 			//	Add this as the last line in your rules.
	 * 			'<view:\w+>' => 'default/_static',
	 * 		),
	 *
	 * The above assumes your default controller is DefaultController. If is different
	 * simply change the route above (default/_static) to your default route.
	 *
	 * Finally, create a directory under your default controller's view path:
	 *
	 * 		/path/to/your/app/protected/views/default/_static
	 *
	 * Place your static files in there, for example:
	 *
	 * 		/path/to/your/app/protected/views/default/_static/aboutUs.php
	 * 		/path/to/your/app/protected/views/default/_static/contactUs.php
	 * 		/path/to/your/app/protected/views/default/_static/help.php
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
			), parent::actions()
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
	public function genericAction( $actionId, $model = null, $extraParameters = array( ), $modelVariableName = 'model', $flashMessageKey = null, $flashMessageValue = null, $flashMessageDefaultValue = null )
	{
		if ( $flashMessageKey ) 
			XL::_sf( $flashMessageKey, $flashMessageValue, $flashMessageDefaultValue );
		
		$this->render( 
			$actionId, 
			array_merge( 
				$extraParameters, 
				array( 
					$modelVariableName => ( $model ) ? $model : $this->loadCurrentModel() 
				)
			)
		);
	}

	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 *
	 * @param integer the primary key value. Defaults to null, meaning using the 'id' GET variable
	 * @throws CHttpException
	 */
	public function loadCurrentModel( $id = null )
	{
		if ( null === $this->_currentModel )
		{
			$_id = XL::o( $_GET, 'id', $id );
			$this->_currentModel = $this->_load( $_id );

			//	No data? bug out
			if ( null === $this->_currentModel )
				$this->redirect( array( $this->defaultAction ) );

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
		if ( null === ( $_error = XL::_gco( 'errorHandler' )->error ) )
		{
			if ( ! $this->isAjaxRequest() ) 
				throw new CHttpException( 404, 'Page not found.' );
			
			echo $_error['message'];
		}

		$this->render( 
			'error', 
			array( 
				'error' => $_error 
			)
		);
	}

	/**
	 * Convenience access to Yii request
	 *
	 */
	public function getRequest()
	{
		return XL::_gr();
	}

	/**
	 * See if there are any commands that need processing
	 * @param CAction $action
	 * @return boolean
	 */
	public function beforeAction( $action )
	{
		//	If we have commands, give it a shot...
		if ( count( $this->_adminCommandMap ) && parent::beforeAction( $action ) ) 
			$this->_processCommand();

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
			if ( $this->_contentTemplate && false !== ( $_contentTemplateFile = $this->getLayoutFile( $this->_contentTemplate ) ) ) $_output = $this->renderPartial( $_contentTemplateFile, $viewData, true );

			$_output = $this->renderFile( $_layoutFile, array( 'content' => $_output ), true );
			$_output = $this->processOutput( $_output );
		}

		if ( $returnString ) return $_output;

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
					self::CLASS_LOG_TAG,
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
	public function setStandardFormOptions( $model, $optionList = array( ) )
	{
		//	Shortcut... only passed in the title...
		if ( is_string( $optionList ) )
		{
			$_title = $optionList;

			$optionList = array(
				'title' => XL::_gan() . ' :: ' . $_title,
				'breadcrumbs' => array( $_title ),
			);
		}

		//	Abbreviated arguments?
		if ( is_array( $model ) && array( ) === $optionList )
		{
			$optionList = $model;
			$model = XL::o( $optionList, 'model' );
		}

		//	Set the standard nav options
		$this->setViewNavigationOptions( $optionList );

		$_formId = XL::o( $optionList, 'id', 'ps-edit-form' );

		//	Put a cool flash span on the page
		if ( XL::o( $options, 'enableFlash', true, true ) )
		{
			$_flashClass = XL::o( $options, 'flashSuccessClass', 'operation-result-success' );

			if ( null === ( $_message = XL::_gf( 'success' ) ) )
			{
				if ( null !== ( $_message = XL::_gf( 'failure' ) ) ) $_flashClass = XL::o( $options, 'flashFailureClass', 'operation-result-failure' );
			}

			$_spanId = XL::o( $options, 'flashSpanId', 'operation-result', true );
			XL::_ss( 'psForm-flash-html', XL::tag( 'span', array( 'id' => $_spanId, 'class' => $_flashClass ), $_message ) );

			//	Register a nice little fader...
			$_fader = <<<SCRIPT
$('#{$_spanId}').fadeIn('500',function(){
	$(this).delay(3000).fadeOut(3500);
});
SCRIPT;

			XL::_rs( $_formId . '.' . $_spanId . '.fader', $_fader, CClientScript::POS_READY );
		}

		XL::setFormFieldContainerClass( XL::o( $optionList, 'rowClass', 'row' ) );

		$_formOptions = array(
			'id' => $_formId,
			'showLegend' => XL::o( $optionList, 'showLegend', true ),
			'showDates' => XL::o( $optionList, 'showDates', false ),
			'method' => XL::o( $optionList, 'method', 'POST' ),
			'uiStyle' => XL::o( $optionList, 'uiStyle', XL::UI_JQUERY ),
			'formClass' => XL::o( $optionList, 'formClass', 'form' ),
			'formModel' => $model,
			'errorCss' => XL::o( $optionList, 'errorCss', 'error' ),
			//	We want error summary...
			'errorSummary' => XL::o( $optionList, 'errorSummary', true ),
			'errorSummaryOptions' => array(
				'header' => '<p>The following problems occurred:</p>',
			),
			'validate' => XL::o( $optionList, 'validate', true ),
			'validateOptions' => XL::o( $optionList, 'validateOptions', array(
				'ignoreTitle' => true,
				'errorClass' => 'ps-validate-error',
				)
			),
		);

		//	Do some auto-page-setup...
		if ( null !== ( $_header = XL::o( $optionList, 'header', XL::o( $optionList, 'title' ) ) ) )
		{
			if ( null !== ( $_headerIcon = XL::o( $optionList, 'headerIcon' ) ) ) $_header = XL::tag( 'span', array( ), XL::image( $_headerIcon ) ) . $_header;

			echo XL::tag( 'h1', array( 'class' => 'ui-generated-header' ), $_header );
		}

		//	Do some auto-page-setup...
		if ( null !== ( $_subHeader = XL::o( $optionList, 'subHeader' ) ) ) echo XL::tag( 'div', array( ), $_subHeader );

		if ( false !== XL::o( $optionList, 'renderSearch', false ) )
		{
			echo XL::tag( 'p', array( ), self::SEARCH_HELP_TEXT );
			echo XL::link( 'Advanced Search', '#', array( 'class' => 'search-button' ) );

			echo XL::tag(
				'div', array( 'class' => 'search-form' ), $this->renderPartial( '_search', array( 'model' => $model ), true )
			);

			//	Register the search script, if any
			if ( null !== ( $_searchScript = XL::o( $optionList, '__searchScript' ) ) ) XL::_rs( 'search', $_searchScript );
		}

		if ( XL::UI_JQUERY == ( $_uiStyle = XL::o( $optionList, 'uiStyle', XL::UI_JQUERY ) ) ) CPSjqUIWrapper::loadScripts();

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
	public function setViewNavigationOptions( &$options = array( ) )
	{
		//	Page title
		$_title = XL::o( $options, 'title', null, true );
		$_subtitle = XL::o( $options, 'subtitle', null, true );
		$_header = XL::o( $options, 'header' );

		//	Generate subtitle from header...
		if ( null === $_title && null === $_subtitle && null !== $_header ) $_subtitle = $_header;

		if ( $_subtitle ) $_title = XL::_gan() . ' :: ' . $_subtitle;

		if ( !$_title ) $_title = XL::_gan();

		$this->setPageTitle( $options['title'] = $_title );

		//	Set crumbs
		$this->_breadcrumbs = XL::o( $options, 'breadcrumbs' );

		//	Let side menu be set from here as well...
		if ( null !== ( $_menuItems = XL::o( $options, 'menu', null ) ) )
		{
			//	Rebuild menu items if not in standard format
			$_finalMenu = array( );

			foreach ( $_menuItems as $_itemLabel => $_itemLink )
			{
				if ( null === ( $_label = XL::o( $_itemLink, 'label', null, true ) ) ) $_label = $_itemLabel;

				if ( null === ( $_link = XL::o( $_itemLink, 'link', null, true ) ) ) $_link = $_itemLink;

				$_finalMenu[] = array(
					'label' => $_label,
					'url' => $_link,
				);
			}

			$options['menu'] = $this->_menu = $_finalMenu;
		}

		$_enableSearch = ( XL::o( $options, 'enableSearch', false ) || XL::o( $options, 'renderSearch', false ) );

		//	Drop the search script on the page if enabled...
		if ( false !== $_enableSearch )
		{
			$_searchSelector = XL::o( $options, 'searchSelector', '.search-button' );
			$_toggleSpeed = XL::o( $options, 'toggleSpeed', 'fast' );
			$_searchForm = XL::o( $options, 'searchForm', '.search-form' );
			$_targetFormId = XL::o( $options, 'id', 'ps-edit-form' );

			$_searchScript = <<<JS
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
	//* Property Accessors
	//********************************************************************************

	/**
	 * @addtogroup Properties
	 * @brief Properties of YiiXL Classes
	 * @{
	 */
	/**
	 * 	@defgroup propaccs_cxlcontroller CXLController Properties
	 * 	@brief These are the properties accessors for the CXLController
	 * 	@{
	 */

	/**
	 * Convenience access to isPostRequest
	 * @return boolean
	 */
	public function getIsPostRequest()
	{
		return XL::isPostRequest();
	}

	/**
	 * Convenience access to isAjaxRequest
	 * @return boolean
	 */
	public function getIsAjaxRequest()
	{
		return XL::isAjaxRequest();
	}

	/**
	 * Returns the base url of the current app
	 * @return string
	 */
	public function getAppBaseUrl()
	{
		return XL::_gbu();
	}

	/**
	 * @return array
	 */
	public function getMenu()
	{
		return $this->_menu;
	}

	/**
	 * @param  $value
	 * @return CXLController
	 */
	public function setMenu( $value )
	{
		$this->_menu = $value;
		return $this;
	}

	/**
	 * @return array
	 */
	public function getBreadcrumbs()
	{
		return $this->_breadcrumbs;
	}

	/**
	 * @param  $value
	 * @return CXLController
	 */
	public function setBreadcrumbs( $value )
	{
		$this->_breadcrumbs = $value;
		return $this;
	}

	/**
	 * @return
	 */
	public function getPageHeading()
	{
		return $this->m_sPageHeading;
	}

	/**
	 * @param  $value
	 * @return CXLController
	 */
	public function setPageHeading( $value )
	{
		$this->m_sPageHeading = $value;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getActionMethodPrefix()
	{
		return $this->_actionMethodPrefix;
	}

	/**
	 * @param  $value
	 * @return CXLController
	 */
	public function setActionMethodPrefix( $value )
	{
		$this->_actionMethodPrefix = $value;
		return $this;
	}

	/**
	 * @return CActiveRecord|null
	 */
	public function getCurrentModel()
	{
		return $this->_currentModel;
	}

	/**
	 * @param  $value
	 * @return CXLController
	 */
	protected function setCurrentModel( $value )
	{
		$this->_currentModel = $value;
		return $this;
	}

	/**
	 * @return null|string
	 */
	public function getModelClass()
	{
		return $this->_modelClass;
	}

	/**
	 * @param  $value
	 * @return CXLController
	 */
	protected function setModelClass( $value )
	{
		$this->_modelClass = $value;
		return $this;
	}

	/**
	 * @return null|string
	 */
	public function getContentTemplate()
	{
		return $this->_contentTemplate;
	}

	/**
	 * @param  $value
	 * @return CXLController
	 */
	public function setContentTemplate( $value )
	{
		$this->_contentTemplate = $value;
		return $this;
	}

	/**
	 * @return bool
	 */
	public function getAutoFindLayout()
	{
		return $this->_autoFindLayout;
	}

	/**
	 * @param  $value
	 * @return CXLController
	 */
	public function setAutoFindLayout( $value )
	{
		$this->_autoFindLayout = $value;
		return $this;
	}

	/**
	 * @return bool
	 */
	public function getAutoFindAction()
	{
		return $this->_autoFindAction;
	}

	/**
	 * @param  $value
	 * @return $this
	 */
	public function setAutoFindAction( $value )
	{
		$this->_autoFindAction = $value;
		return $this;
	}

	/**
	 * @return array
	 */
	public function getAdminCommandMap()
	{
		return $this->_adminCommandMap;
	}

	/**
	 * @param  $value
	 * @return CXLController
	 */
	public function setAdminCommandMap( $value )
	{
		$this->_adminCommandMap = $value;
		return $this;
	}

	/**
	 * @param string $key
	 * @param mixed $value
	 * @param string $property
	 * @return CXLController
	 */
	public function addCommandToMap( $key, $value = null, $property = null )
	{
		$this->_adminCommandMap[$key] = $value;

		if ( $property )
			$this->addActionControls( $property, array( $key ) );

		return $this;
	}

	/**
	 * @return array
	 */
	public function getPortletActions()
	{
		return $this->_portletActions;
	}

	/**
	 * @param  $value
	 * @return CXLController
	 */
	public function setPortletActions( $value )
	{
		$this->_portletActions = $value;
		return $this;
	}

	/**
	 * @param  $sName
	 * @param  $arCallback
	 * @return CXLController
	 */
	public function addPortletAction( $sName, $arCallback )
	{
		$this->_portletActions[$sName] = $arCallback;
		return $this;
	}

	/**
	 * @return array
	 */
	protected function getViewData()
	{
		return $this->_viewData;
	}

	/**
	 * @param  $value
	 * @return CXLController
	 */
	protected function setViewData( $value )
	{
		$this->_viewData = $value;
		return $this;
	}

	/**
	 * @return array
	 */
	protected function getExtraViewDataList()
	{
		return $this->_extraViewDataList;
	}

	/**
	 * @param  $value
	 * @return CXLController
	 */
	protected function setExtraViewDataList( $value )
	{
		$this->_extraViewDataList = $value;
		return $this;
	}

	/**
	 * @return string
	 */
	protected function getExtraViewDataPrefix()
	{
		return $this->_extraViewDataPrefix;
	}

	/**
	 * @param  $value
	 * @return CXLController
	 */
	protected function setExtraViewDataPrefix( $value )
	{
		$this->_extraViewDataPrefix = $value;
		return $this;
	}

	/**
	 *	@}
	 * @}
	 */

	//********************************************************************************
	//* Private Methods
	//********************************************************************************

	/**
	 * Executes any commands
	 * Maps to {@link CXLController::commandMap} and calls the appropriate method.
	 *
	 * @return mixed
	 */
	protected function _processCommand( $arData = array( ), $sIndexName = self::COMMAND_FIELD_NAME )
	{
		//	Our return variable
		$_oResults = null;

		//	Get command's method...
		$_sCmd = XL::o( $_REQUEST, $sIndexName );

		//	Do we have a command mapping?
		if ( null !== ( $_arCmd = XL::o( $this->_adminCommandMap, $_sCmd ) ) )
		{
			//	Get any miscellaneous data into the appropriate array
			if ( count( $arData ) )
			{
				if ( $this->getIsPostRequest() ) $_POST = array_merge( $_POST, $arData );
				else $_GET = array_merge( $_GET, $arData );
			}

			$_oResults = call_user_func( $_arCmd[1] );
		}

		//	Return the results
		return $_oResults;
	}

	/**
	 * Saves the data in the model
	 *
	 * @param CModel $model The model to save
	 * @param array $arData The array of data to merge with the model
	 * @param string $sRedirectAction Where to redirect after a successful save
	 * @param boolean $bAttributesSet If true, attributes will not be set from $arData
	 * @param string $sModelName Optional model name
	 * @param string $sSuccessMessage Flash message to set if successful
	 * @param boolean $bNoCommit If true, transaction will not be committed
	 * @return boolean
	 */
	protected function _saveCurrentModel( &$model, $arData = array( ), $sRedirectAction = 'show', $bAttributesSet = false, $sModelName = null, $sSuccessMessage = null, $bNoCommit = false,
									  $bSafeOnly = false )
	{
		$_sMessage = XL::nvl( $sSuccessMessage, 'Your changes have been saved.' );
		$_sModelName = XL::nvl( $sModelName, XL::nvl( $model->getModelClass(), $this->_modelClass ) );

		if ( isset( $arData, $arData[$_sModelName] ) )
		{
			if ( !$bAttributesSet ) $model->setAttributes( $arData[$_sModelName], $bSafeOnly );

			if ( $model->save() )
			{
				if ( !$bNoCommit && $model instanceof CPSModel && $model->hasTransaction() ) $model->commitTransaction();

				Yii::app()->user->setFlash( 'success', $_sMessage );

				if ( $sRedirectAction ) $this->redirect( array( $sRedirectAction, 'id' => $model->id ) );

				return true;
			}
		}

		return false;
	}

	/*	 * *
	 * Just like saveModel, but doesn't commit, and never redirects.
	 *
	 * @param CPSModel $model
	 * @param array $arData
	 * @param boolean $bAttributesSet
	 * @param string $sSuccessMessage
	 * @return boolean
	 * @see saveModel
	 */

	protected function _saveTransactionCurrentModel( &$model, $arData = array( ), $bAttributesSet = false, $sSuccessMessage = null )
	{
		return $this->_saveCurrentModel( $model, $arData, false, $bAttributesSet, null, $sSuccessMessage, true );
	}

	/**
	 * Loads a page of models
	 * @param boolean Whether or not to apply a sort. Defaults to false
	 *
	 * @return array Element 0 is the results of the find. Element 1 is the pagination object
	 */
	protected function _loadPaged( $bSort = false, $oCriteria = null )
	{
		$_oSort = $_oCrit = $_oPage = null;

		//	Make criteria
		$_oCrit = XL::nvl( $oCriteria, new CDbCriteria() );
		$_oPage = new CPagination( $this->_loadCount( $_oCrit ) );
		$_oPage->pageSize = XL::o( $_REQUEST, 'perPage', self::PAGE_SIZE );
		if ( isset( $_REQUEST, $_REQUEST['page'] ) ) $_oPage->setCurrentPage( intval( $_REQUEST['page'] ) - 1 );
		$_oPage->applyLimit( $_oCrit );

		//	Sort...
		if ( $bSort )
		{
			$_oSort = new CPSSort( $this->_modelClass );
			$_oSort->applyOrder( $_oCrit );
		}

		//	Return an array of what we've build...
		return array( $this->_loadAll( $_oCrit ), $_oCrit, $_oPage, $_oSort );
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
	protected function _genericModelLoad( $sMethod, &$oCrit = null, $arScope = array( ), $arOptions = array( ) )
	{
		$_sMethod = $this->_getModelLoadString( $arScope, $arOptions ) . $sMethod;
		return eval( "return (" . $_sMethod . ");" );
	}

	/**
	 * This method reads the data from the database and returns the row.
	 * Must override in subclasses.
	 * @var integer $id The primary key to look up
	 * @return CActiveRecord
	 */
	protected function _load( $id = null )
	{
		return $this->_genericModelLoad( 'findByPk(' . $id . ')' );
	}

	/**
	 * Loads all data using supplied criteria
	 * @param CDbCriteria $oCrit
	 * @return array Array of CActiveRecord
	 * @todo When using PHP v5.3, {@link eval} will no longer be needed
	 */
	protected function _loadAll( &$oCrit = null )
	{
		return $this->_genericModelLoad( 'findAll(' . ( null !== $oCrit ? '$oCrit' : '' ) . ')', $oCrit );
	}

	/**
	 * Returns the count of rows that match the supplied criteria
	 *
	 * @param CDbCriteria $oCrit
	 * @return integer The number of rows
	 */
	protected function _loadCount( &$oCrit = null )
	{
		$_sCrit = ( $oCrit ) ? '$oCrit' : null;
		return $this->_genericModelLoad( 'count(' . $_sCrit . ')', $oCrit );
	}

	/**
	 * Builds a string suitable for {@link eval}. The verb is intentionally not appeneded.
	 *
	 * @param array $arScope
	 * @return string
	 * @todo Will be deprecated after upgrade to PHP v5.3
	 */
	protected function _getModelLoadString( $arScope = array( ), $arOptions = array( ) )
	{
		$_sScopes = ( count( $arScope ) ) ? implode( '->', $arScope ) . '->' : null;
		return $this->_modelClass . '::model()->' . $_sScopes;
	}

	/**
	 * Pushes an action onto the action queue
	 *
	 * @param CAction $action
	 */
	protected function _pushAction( $action )
	{
		array_push( $this->_actionStack, $action );
	}

	/**
	 * Retrieves the latest pushed action
	 * @return CAction
	 */
	protected function _popAction()
	{
		return array_pop( $this->_actionStack );
	}

	/**
	 * Pushes a variable onto the view data stack
	 *
	 * @param string $variableName
	 * @param mixed $variableData
	 */
	protected function _addViewData( $variableName, $variableData = null )
	{
		$this->_viewData[$variableName] = $variableData;
	}

	/**
	 * Clears the current search criteria
	 * @return null
	 */
	protected function _clearSearchCriteria()
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

		if ( false === $payload || true === $payload ) $payload = ( $payload ? '1' : '0' );

		$_result = json_encode( $payload );
		if ( $encode ) $_result = htmlspecialchars( $_result, $encodeOptions );

		echo $_result;
		return;
	}

}