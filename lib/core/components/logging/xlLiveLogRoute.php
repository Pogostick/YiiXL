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
 * @package			yiixl.core.components.logging
 * @category		Components
 * @since			v1.0.0
 *
 * @filesource
 */
/**
 * xlLiveLogRoute
 * The real-time file log route with regex exclusion filters
 * 
 * @property array $excludeCategories
 * @property boolean $abbreviateLevel
 * @property integer $categoryWidth
 * @property string $dateFormat
 * @property string $outputFormat
 */
class xlLiveLogRoute extends CFileLogRoute implements xlILog, xlIComponent
{
	//********************************************************************************
	//* Constants
	//********************************************************************************

	/**
	 * Our logging tag
	 */
	const 
		CLASS_LOG_TAG = 'yiixl.core.components.logging.xlLiveLogRoute'
	;

	//********************************************************************************
	//* Private Members
	//********************************************************************************

	/**
	 * @var array An array of categories to exclude from logging. Regex pattern matching is supported via {@link preg_match}
	 */
	protected $_excludeCategories = array();
	/**
	 * @var boolean If true, only the 3-character, abbreviated level is displayed (i.e. Inf, Dbg, etc) If false, full level name will be displayed (i.e. INFO, DEBUG, etc.)
	 */
	protected $_abbreviateLevel = true;
	/**
	 * @var integer The minimum width of the category column in the log output
	 */
	protected $_categoryWidth = 35;
	/**
	 * @var string The date format for log entries. Replaced in $outputFormat at log time
	 */
	protected $_dateFormat = 'M d H:i:s.u';
	/**
	 * @var string The output format for log entries
	 */
	protected $_outputFormat = '[%%DATE%%][%%LEVEL%%][%%CATEGORY%%] %%MESSAGE%%';

	//********************************************************************************
	//* Public Methods
	//********************************************************************************

	/**
	 * Initialize the component
	 * @return void
	 */
	public function init()
	{
		parent::init();

		//	Live log
		Yii::getLogger()->autoDump = true;
		Yii::getLogger()->autoFlush = 1;
	}

	/**
	 * Retrieves filtered log messages from logger for further processing.
	 * @param CLogger $logger logger instance
	 * @param boolean $processLogs whether to process the logs after they are collected from the logger. ALWAYS TRUE NOW!
	 */
	public function collectLogs( $logger, $processLogs = false /* ignored */ )
	{
		parent::collectLogs( $logger, true );
	}

	//********************************************************************************
	//* Private Methods
	//********************************************************************************

	/**
	 * Writes log messages in files.
	 * @param array $logs list of log messages
	 */
	protected function processLogs( $logs = array() )
	{
		try
		{
			$_logFile = $this->getLogPath() . DIRECTORY_SEPARATOR . $this->getLogFile();

			if ( @filesize( $_logFile ) > $this->getMaxFileSize() * 1024 ) $this->rotateFiles();

			//	Write out the log entries
			foreach ( $logs as $_log )
			{
				$_exclude = false;

				//	Check out the exclusions
				if ( !empty( $this->_excludeCategories ) )
				{
					foreach ( $this->_excludeCategories as $_category )
					{
						//	If found, we skip
						if ( trim( strtolower( $_category ) ) == trim( strtolower( $_log[2] ) ) )
						{
							$_exclude = true;
							break;
						}

						//	Check for regex
						if ( '/' == $_category[0] && 0 != @preg_match( $_category, $_log[2] ) )
						{
							$_exclude = true;
							break;
						}
					}
				}

				/**
				 * 	Use {@link error_log} facility to write out log entry
				 */
				if ( !$_exclude ) error_log( $this->formatLogMessage( $_log[0], $_log[1], $_log[2], $_log[3] ), 3, $_logFile );
			}

			//	Processed, clear!
			$this->logs = null;
		}
		catch ( Exception $_ex )
		{
			error_log( __METHOD__ . ': Exception processing application logs: ' . $_ex->getMessage() );
		}
	}

	/**
	 * Formats a log message given different fields.
	 * @param string $message message content
	 * @param int|string $level message level
	 * @param string $category message category
	 * @param integer $time timestamp
	 * @return string formatted message
	 */
	protected function formatLogMessage( $message, $level = 'I', $category = null, $time = null )
	{
		if ( null === $time )
		{
			$time = time();
		}

		return str_ireplace(
			array(
				'%%DATE%%',
				'%%LEVEL%%',
				'%%CATEGORY%%',
				'%%MESSAGE%%',
			),
			array(
				@date( $this->_dateFormat, $time ),
				( $this->_abbreviateLevel ? $level : strtoupper( $level[0] ) ),
				sprintf( '%' . $this->_categoryWidth . '.' . $this->_categoryWidth . 's', $category ),
				$message
			),
			$this->_outputFormat
		
		) . PHP_EOL;
	}

	//********************************************************************************
	//* Property Accessors
	//********************************************************************************

	/**
	 * @return array
	 */
	public function getExcludeCategories()
	{
		return $this->_excludeCategories;
	}

	/**
	 * @param $value
	 * @return \xlLiveLogRoute
	 */
	public function setExcludeCategories( $value )
	{
		$this->_excludeCategories = $value;
		return $this;
	}

	/**
	 * Get the minimum width of the category column in the log output
	 * @return integer
	 */
	public function getCategoryWidth()
	{
		return $this->_categoryWidth;
	}

	/**
	 * Set the minimum width of the category column in the log output
	 * @param $value
	 * @return integer
	 */
	public function setCategoryWidth( $value )
	{
		$this->_categoryWidth = $value;
	}

	/**
	 * @param boolean $abbreviateLevel
	 * @return \xlLiveLogRoute $this
	 */
	public function setAbbreviateLevel( $abbreviateLevel )
	{
		$this->_abbreviateLevel = $abbreviateLevel;
		return $this;
	}

	/**
	 * @return boolean
	 */
	public function getAbbreviateLevel()
	{
		return $this->_abbreviateLevel;
	}

	/**
	 * @param string $dateFormat
	 * @return \xlLiveLogRoute $this
	 */
	public function setDateFormat( $dateFormat )
	{
		$this->_dateFormat = $dateFormat;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getDateFormat()
	{
		return $this->_dateFormat;
	}

	/**
	 * @param string $outputFormat
	 * @return \xlLiveLogRoute $this
	 */
	public function setOutputFormat( $outputFormat )
	{
		$this->_outputFormat = $outputFormat;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getOutputFormat()
	{
		return $this->_outputFormat;
	}

}