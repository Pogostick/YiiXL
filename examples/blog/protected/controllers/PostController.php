<?php
/*
 * This file was generated by the yiixl scaffolding package.
 * 
 * @copyright Copyright &copy; 2009 My Company, LLC.
 * @link http://www.example.com
 */

/**
 * PostController class file
 * 
 * @package 	blog
 * @subpackage 	
 * 
 * @author 		Web Master <webmaster@example.com>
 * @version 	SVN: $Id: PostController.php 367 2010-01-16 04:29:24Z jerryablan@gmail.com $
 * @since 		v1.0.6
 *  
 * @filesource
 * 
 */
class PostController extends CXLHelperBase
{
	//********************************************************************************
	//* Public Methods
	//********************************************************************************
	
	public function init()
	{
		//	Phone home...
		parent::init();
		
		$this->defaultAction = 'list';
		$this->addUserActions( self::ACCESS_TO_ALL, array( 'captcha', '__themeChangeRequest', 'postsByDate', 'jquiExamples' ) );
		$this->setUserActionList( self::ACCESS_TO_AUTH, array() );
		
		//	Set model name...
		$this->setModelName( 'Post' );
	}

	//********************************************************************************
	//* Private Methods
	//********************************************************************************
	
	/**
	* Create a new comment
	* 
	* @param Post $oPost
	* @return Comment
	*/
	protected function newComment( Post $oPost )
	{
		$_oComment = new Comment();
		
		if ( isset( $_POST, $_POST['Comment'] ) )
		{
			//	Use setAttributes!
			$_oComment->setAttributes( $_POST['Comment'] );
			$_oComment->post_id = $oPost->id;
			$_oComment->status_nbr = Comment::STATUS_PENDING;

			if ( $_oComment->save() )
			{
				Yii::app()->user->setFlash( 'commentSubmitted', 'Thank you. Your comment has been saved.' );
				$this->refresh();
			}
		}

		return $_oComment;
	}

	/**
	* Check if user is logged in.
	* @returns Post
	*/
	public function loadModel( $iId = null )
	{
		if ( null === ( $_oModel = parent::loadModel( $iId ) ) )
		{
			if ( Yii::app()->user->isGuest && $_oModel->status_nbr != Post::STATUS_PUBLISHED )
				throw new CHttpException( 404, 'The requested post does not exist.' );
		}
		
		return $_oModel;
	}

	/**
	 * Generates the hyperlinks for post tags.
	 * This is mainly used by the view that displays a post.
	 * @param Post the post instance
	 * @return string the hyperlinks for the post tags
	 */
	public function getTagLinks( $oPost )
	{
		$_arLinks = array();
		
		foreach ( $oPost->getTagArray() as $_sTag )
			$_arLinks[] = YiiXL::link( YiiXL::encode( $_sTag ), array( 'list', 'tag' => $_sTag ) );
			
		return implode( ', ', $_arLinks );
	}

	//********************************************************************************
	//* Actions
	//********************************************************************************
	
	/**
	 * Declares class-based actions.
	 */
	public function actions()
	{
		return array(
			// captcha action renders the CAPTCHA image
			// this is used by the contact page
			'captcha' => array(
				'class' => 'CCaptchaAction',
				'transparent' => true,
				'minLength' => 10,
				'maxLength' => 15,
				'width' => 200,
			),
		);
	}

	/**
	* Shows a post
	* 
	*/
	public function actionShow( $arExtraParams = array() )
	{
		$_oPost = $this->loadModel();
		$_oComment = $this->newComment( $_oPost );
	    $this->render( 'show', array_merge( $arExtraParams, array( 'post' => $_oPost, 'comments' => $_oPost->comments, 'newComment' => $_oComment ) ) );
	}
	
	/**
	* Creates a post
	*/
	public function actionCreate( $arExtraParams = array() )
	{
		$_oPost = new Post();
		
		if ( isset( $_POST, $_POST['Post'] ) )
		{
			$_oPost->setAttributes( $_POST['Post'] );
			
			if ( isset( $_POST['previewPost'] ) )
				$_oPost->validate();
			else 
			{
				try
				{
					$_oPost->beginTransaction();
					
					if ( $this->saveTransactionModel( $_oPost, $_POST, 'show', true ) )
					{
						//	Delete all tag associations
						PostTag::model()->deleteAll( 'post_id = :post_id', array( ':post_id' => $this->id ) );

						//	Get the tags from this post and create associations
						$_arTags = $_oPost->getTagArray();
						
						foreach ( $_arTags as $_sName )
						{
							//	If tag doesn't exist, create it.
							if ( null === ( $_oTag = Tag::model()->findByAttributes( array( 'tag_name_text' => $_sName ) ) ) )
							{
								$_oTag = new Tag( array( 'tag_name_text' => $_sName ) );
								$_oTag->save();
							}
							
							//	Create associated entity...
							$_oPostTag = new PostTag();
							$_oPostTag->post_id = $_oPost->id;
							$_oPostTag->tag_id = $_oTag->id;
							$_oPostTag->save();
						}
						
						//	And commit...
						$_oPost->commitTransaction();
						
						//	Redirect to show...
						$this->redirect( array( 'show', 'id' => $_oPost->id ) );
					}
					
				}
				catch ( Exception $_ex )
				{
					//	Something bad happened...
					$_oPost->rollbackTransaction();
					throw $_ex;
				}
			}
		}
		
		$this->render( 'create', array_merge( $arExtraParams, array( 'model' => $_oPost ) ) );
	}

	/**
	* Updates a post
	*/
	public function actionUpdate( $arExtraParams = array() )
	{
		$_iId = YiiXL::o( $_GET, 'id' );
		
		if ( null !== ( $_oPost = Post::model()->findByPk( $_iId ) ) )
		{
			if ( isset( $_POST, $_POST['Post'] ) )
			{
				$_oPost->attributes = $_POST['Post'];

				if ( isset( $_POST['previewPost'] ) )
					$_oPost->validate();
				else 
				{
					if ( isset( $_POST['submitPost'] ) && $_oPost->save() )
						$this->redirect( array( 'show', 'id' => $_oPost->id ) );
				}
			}
		}
		
		$this->render( 'update', array_merge( $arExtraParams, array( 'model' => $_oPost ) ) );
	}

	/**
	* List view
	*/
	public function actionList( $arExtraParams = array() )
	{ 
		$_oCrit = new CDbCriteria;
		$_arWith = array( 'author' );
		
		if ( $_sPostDate = YiiXL::o( $_GET, 'date' ) )
			$_oCrit->mergeWith( new CDbCriteria( array( 'condition' => 'date(t.create_date) = date(:post_date)', 'params' => array( ':post_date' => $_sPostDate ) ) ) );
		
		if ( $_sTag = YiiXL::o( $_GET, 'tag' ) )
		{
			$_oCrit->distinct = true;
			$_arWith['tagFilter']['distinct'] = true;
			$_arWith['tagFilter']['params'][':tag_name_text'] = $_sTag;
			$_iCount = Post::model()->published()->with( $_arWith )->count();
		}
		else
			$_iCount = Post::model()->published()->count( $_oCrit );

		$_oPages = new CPagination( $_iCount );
		$_oPages->pageSize = Yii::app()->params['postsPerPage'];
		$_oPages->applyLimit( $_oCrit );

		$_arModel = Post::model()->published()->with( $_arWith )->findAll( $_oCrit );
		
		$this->render( 'list', array( 'posts' => $_arModel, 'pages' => $_oPages, 'postDate' => $_sPostDate ) );
	}

	/**
	 * Publishes a post
	 */
	public function actionPublish()
	{
		if ( $_oPost = $this->loadModel() )
		{
			$_oPost->status_nbr = Post::STATUS_PUBLISHED;
			$_oPost->save();
		}
		
		$this->actionAdmin();
	}
	
	/**
	 * Get a list of posts on a day
	 * 
	 */
	public function actionPostsByDate()
	{
		$this->redirect( array( '/date/' . date( 'Y-m-d', strtotime( YiiXL::o( $_POST, 'dateValue' ) ) ) ) );
	}
	
	/**
	 * Show's the jQuery UI extension wrapper examples from the pYe
	 */
	public function actionJquiExamples()
	{
		$this->render( 'pogostick.widgets.examples.jqui_examples' );
	}
	
	/**
	 * User has requested a new theme
	 * @returns bool
	 */
	public function handleThemeChangeRequest()
	{
		if ( null !== ( $_oRoller = Yii::app()->getComponent( 'psThemeRoller', false ) ) )
			$_oRoller->handleThemeChangeRequest();
	}
}