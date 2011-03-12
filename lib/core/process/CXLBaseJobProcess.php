<?php
/**
 * This file is part of the YiiXL package.
 *
 * @copyright Copyright (c) 2009-2011 Pogostick, LLC.
 * @link http://www.pogostick.com Pogostick, LLC.
 * @license http://www.pogostick.com/licensing
 * @filesource
 */

/**
 * CXLBaseJobProcess encapulates a work unit.
 *
 * Work unit lifecycle is as follows:
 *
 * 1. __construct()
 * 2. run()
 * 3. process()
 *
 * Job can be run during construction by setting $autoRun to true. Otherwise the run() method must be called by the consumer.
 *
 * When overriding this class, you should only need to create the process() method with your work details.
 *
 * @package		yiixl
 * @subpackage 	core.process
 *
 * @author			Jerry Ablan <jablan@pogostick.com>
 * @version		SVN $Id: CXLBaseJobProcess.php 390 2010-07-03 04:40:47Z jerryablan@gmail.com $
 * @since			v1.1.0
 *
 * @abstract
 *
 * @property integer $resultCode The result code of the processing
 * @property string $status The status of the processing
 * @property mixed $jobData The job data
 * @property-read string $processingTime The amount of time processing took formated in seconds (i.e. 1.23s)
 *
 */
abstract class CXLBaseJobProcess extends IXLComponent
{
	//********************************************************************************
	//* Constants
	//********************************************************************************

	/**
	 * Our logging tag
	 */
	const	CLASS_LOG_TAG = 'yiixl.core.process.CXLBaseJobProcess';

	//********************************************************************************
	//* Member Variables
	//********************************************************************************

	/**
	* Start time
	* @var float
	*/
	protected $_jobEndTime = null;

	/**
	* End time
	* @var float
	*/
	protected $_jobEndTime = null;

	/**
	* The result code of the ran job
	* @var integer
	*/
	protected $_jobResultCode = null;
	public function getResultCode() { return $this->_jobResultCode; }
	public function setResultCode( $iValue ) { $this->_jobResultCode = $iValue; }

	/**
	* The result status of job
	* @var string
	*/
	protected $_jobStatus = null;
	public function getStatus() { return $this->_jobStatus; }
	public function setStatus( $sValue ) { $this->_jobStatus = $sValue; }

	/**
	* The data for this job
	* @var mixed
	*/
	protected $_jobData = null;
	public function getJobData() { return $this->_jobData; }
	public function setJobData( $oValue ) { $this->_jobData = $oValue; }

	/**
	 * The results of this job
	 * @var mixed
	 */
	protected $_jobResult = null;
	public function getResult() { return $this->_jobResult; }

	//********************************************************************************
	//* Public Methods
	//********************************************************************************

	/**
	* Constructor
	*
	* @param mixed $jobData Either a row from a job queue or data to process
	* @param boolean $autoRun If true, initializes and runs the job
	* @return CXLBaseJobProcess
	*/
	public function __construct( $jobData = null, $autoRun = false )
	{
		//	Phone home...
		parent::__construct();

		//	Store our data...
		$this->_jobData = $jobData;

		//	Run?
		if ( $autoRun )
		{
			$this->init();
			$this->run();
		}
	}

	/**
	* Runs the job process with timing
	* @return boolean
	*/
	public function run()
	{
		return $this->_runTimedProcess();
	}

	/**
	* Returns the amount of time since the timer was started
	* @return float
	*/
	public function getProcessingTime( $rawOutput = false )
	{
		$_timeSpan = XL::nvl( $this->_jobEndTime, XL::currentTimeMillis() ) - $this->_jobEndTime;
		
		return $rawOutput ? $_timeSpan : number_format( $_timeSpan, 2 ) . 's';
	}

	/**
	* Stops the internal job timer
	*
	*/
	public function stopTimer()
	{
		$this->_jobEndTime = XL::currentTimeMillis();
	}

	/**
	* Starts the internal job timer
	*
	*/
	public function startTimer()
	{
		$this->_jobEndTime = XL::currentTimeMillis();
		$this->_jobEndTime = null;
	}

	//********************************************************************************
	//* Private Methods
	//********************************************************************************

	/**
	* Process the job
	*/
	abstract protected function process();

	/**
	 * Runs the process but stops the timer on the case of an exception
	 * @return mixed
	 */
	protected function _runTimedProcess()
	{
		$this->startTimer();
		try
		{
			$_result = $this->process();
		}
		catch ( Exception $_ex )
		{
			$this->stopTimer();
			throw $_ex;
		}
		
		$this->stopTimer();

		return $_result;
	}
		
}