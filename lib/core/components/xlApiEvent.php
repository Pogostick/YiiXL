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
 * @package			yiixl.core.components
 * @category		Events
 * @since			v1.0.0
 *
 * @brief
 *
 * @filesource
 */
/**
 * xlApiEvent 
 * Provides specialized events for {@link xlApiBehavior}
 
 * @property string $url The API call url
 * @property string $query The API call query
 * @property string $results The API call results
 *
 * @todo Find a common base class as this should be a child of xlComponent
 */
class xlApiEvent extends CEvent implements xlIEvent
{
	//********************************************************************************
	//* Members
	//********************************************************************************

	/**
	* The URL being called
	*
	* @var string
	*/
	protected $_url = null;
	/**
	* The query string or post data of the call
	*
	* @var string
	*/
	protected $_query = null;
	/**
	* The results of the call
	*
	* @var mixed
	*/
	protected $_results = null;

	//********************************************************************************
	//* Public Methods
	//********************************************************************************

	/**
	 * Constructor
	 *
	 * @param string|null $url
	 * @param string|null $query
	 * @param string|null $results
	 * @param CComponent $sender
	 * @return xlApiEvent
	 */
	public function __construct( $url = null, $query = null, $results = null, $sender = null )
	{
		parent::__construct( $sender );

		$this->_url = $url;
		$this->_query = $query;
		$this->_results = $results;
	}

	/**
	 * @param string $query
	 * @return \xlApiEvent
	 */
	public function setQuery( $query )
	{
		$this->_query = $query;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getQuery()
	{
		return $this->_query;
	}

	/**
	 * @param mixed $results
	 * @return \xlApiEvent
	 */
	public function setResults( $results )
	{
		$this->_results = $results;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getResults()
	{
		return $this->_results;
	}

	/**
	 * @param string $url
	 * @return \xlApiEvent
	 */
	public function setUrl( $url )
	{
		$this->_url = $url;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getUrl()
	{
		return $this->_url;
	}

}