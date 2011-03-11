<?php
/**
 * This file is part of the YiiXL package.
 *
 * @copyright Copyright (c) 2009-2011 Pogostick, LLC.
 * @link http://www.pogostick.com Pogostick, LLC.
 * @license http://www.pogostick.com/licensing
 * @package yiixl
 * @subpackage logging
 * @filesource
 * @version $Id$
 */

/**
 * CXLLiveLogRoute
 */
class CXLLiveLogRoute extends CFileLogRoute
{
	//********************************************************************************
	//* Constants
	//********************************************************************************
	
	/**
	 * Our logging tag
	 */
	const	CLASS_LOG_TAG = 'yiixl.core.components.logging.CXLLiveLogRoute';
	
	//********************************************************************************
	//* Private Members
	//********************************************************************************

	/**
	 * An array of categories to exclude from logging. Regex pattern matching is supported via {@link preg_match}
	 * @var array 
	 */
	protected $_excludeCategories = array();
	public function getExcludeCategories() { return $this->_excludeCategories; }
	public function setExcludeCategories( $value ) { $this->_excludeCategories = $value; }

	/**
	 * The minimum width of the category column in the log output
	 * @var integer 
	 */
	protected $_categoryWidth = 40;
	/**
	 * Get the minimum width of the category column in the log output
	 * @return integer
	 */
	public function getCategoryWidth() { return $this->_categoryWidth; }
	/**
	 * Set the minimum width of the category column in the log output
	 * @return integer
	 */
	public function setCategoryWidth( $value ) { $this->_categoryWidth = $value; }

	//********************************************************************************
	//* Public Methods
	//********************************************************************************

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

			if ( @filesize( $_logFile ) > $this->getMaxFileSize() * 1024 )
				$this->rotateFiles();

			//	Write out the log entries
			foreach ( $logs as $_log )
			{
				$_exclude = false;

				//	Check out the exclusions
				if ( ! empty( $this->_excludeCategories ) )
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
				if ( ! $_exclude )
					error_log( $this->formatLogMessage( $_log[0], $_log[1], $_log[2], $_log[3] ), 3, $_logFile );
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
	 * @param integer $level message level
	 * @param string $category message category
	 * @param integer $time timestamp
	 * @return string formatted message
	 */
	protected function formatLogMessage( $message, $level = 'I', $category = null, $time = null )
	{
		if ( null === $time )
			$time = time();

		$level = strtoupper( $level[0] );

		return @date( 'M d H:i:s', $time ) . ' [' . sprintf( '%-' . $this->_categoryWidth . 's', $category ) . '] ' . ': <' . $level . '> ' . $message . PHP_EOL;
	}
}