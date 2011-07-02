<?php
/**
 * This file is part of YiiXL
 * Copyright (c) 2009-2011, Pogostick, LLC. All rights reserved.
 *
 * @link http://www.pogostick.com Pogostick, LLC.
 * @license http://www.pogostick.com/licensing
 * @author Jerry Ablan <yiixl@pogostick.com>
 *
 * @since v1.0.0
 *
 * @package yiixl
 * @subpackage core.process
 *
 * @filesource
 */
/**
 * xlBaseJobProcess encapsulates a work unit.
 *
 * Work unit life-cycle is as follows:
 *
 * 1. __construct()
 * 2. run()
 * 3. process()
 *
 * Job can be run during construction by setting $autoRun to true. Otherwise the run() method must be called by the
 * consumer.
 *
 * When overriding this class, you should only need to create the process() method with your work details.
 *
 * @abstract
 * @property integer $resultCode The result code of the processing
 * @property string $status The status of the processing
 * @property mixed $jobData The job data
 * @property bool $runAsChildProcess If true, the process is forked in its own thread
 * @property-read int $childProcessId The pid of the child process
 * @property-read string $processingTime The amount of time processing took formated in seconds (i.e. 1.23s)
 *
 */
abstract class xlBaseJobProcess extends xlComponent
{
	//********************************************************************************
	//* Constants
	//********************************************************************************

	/**
	 * Our logging tag
	 */
	const
		CLASS_LOG_TAG = 'yiixl.core.process.xlBaseJobProcess';

	//********************************************************************************
	//* Member Variables
	//********************************************************************************

	/**
	 * Start time
	 * @var float
	 */
	protected $_jobStartTime = null;
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
	/**
	 * The result status of job
	 * @var string
	 */
	protected $_jobStatus = null;
	/**
	 * The data for this job
	 * @var mixed
	 */
	protected $_jobData = null;
	/**
	 * The results of this job
	 * @var mixed
	 */
	protected $_jobResult = null;
	/**
	 * @var bool
	 */
	protected $_runAsChildProcess = false;
	/**
	 * @var bool
	 */
	protected $_isChild = false;
	/**
	 * @var bool
	 */
	protected $_isRunning = false;
	/**
	 * @var int
	 */
	protected $_pidChild = null;
	/**
	 * @var int
	 */
	protected $_guidChild = null;
	/**
	 * @var int
	 */
	protected $_puidChild = null;

	//********************************************************************************
	//* Public Methods
	//********************************************************************************

	/**
	 * Constructor
	 *
	 * @param mixed $jobData Either a row from a job queue or data to process
	 * @param boolean $autoRun If true, initializes and runs the job
	 * @return xlBaseJobProcess
	 */
	public function __construct( $jobData = null, $autoRun = false )
	{
		//	Phone home...
		parent::__construct();

		//	Store our data...
		$this->_jobData = $jobData;

		if ( $autoRun && $this->_runAsChildProcess )
		{
			pcntl_signal( SIGCHLD, SIG_IGN );

			//	0 = child, else parent
			if ( 0 == ( $_pid = pcntl_fork() ) )
			{
				$this->_isChild = true;
				sleep( 1 );

				// install the signal handler
				pcntl_signal( SIGUSR1, array( $this, 'signalHandler' ) );

				// if requested, change process identity
				if ( null !== $this->_guidChild )
				{
					posix_setgid( $this->_guidChild );
				}

				if ( null !== $this->_puidChild )
				{
					posix_setuid( $this->_puidChild );
				}

				//	kick it off
				$this->init();
				$this->run();
			}
			else
			{
				//	Parent process, return true...
				$this->_isChild = false;
				$this->_pid = $_pid;
			}
		}
		else if ( $autoRun )
		{
			$this->init();
			$this->run();
		}
	}
	
	/**
	 * Runs the job process with timing
	 * @return boolean
	 */
	public
	function run()
	{
		return $this->_runTimedProcess();
	}
	
	/**
	 * Returns the amount of time since the timer was started
	 * @return float
	 */
	public
	function getProcessingTime( $rawOutput = false )
	{
		$_timeSpan = XL::nvl( $this->_jobEndTime, XL::currentTimeMillis() ) - $this->_jobEndTime;
	
		return $rawOutput ? $_timeSpan : number_format( $_timeSpan, 2 ) . 's';
	}
	
	/**
	 * Stops the internal job timer
	 *
	 */
	public
	function stopTimer()
	{
		$this->_jobEndTime = XL::currentTimeMillis();
	}
	
	/**
	 * Starts the internal job timer
	 *
	 */
	public
	function startTimer()
	{
		$this->_jobStartTime = XL::currentTimeMillis();
		$this->_jobStartTime = null;
	}

    /**
     * This is the signal handler that make the communications between client and server possible.<BR>
     * DO NOT override this method, otherwise the thread system will stop working...
     *
     * @param int $signal
     */
    public function signalHandler( $signal )
    {
        switch ( $signal )
		{
            case SIGTERM:
                // handle shutdown tasks
                exit;
                break;

            case SIGHUP:
                // handle restart tasks
                break;

            case SIGUSR1:
				break;

            default:
                // handle all other signals
		}
    }

	//*************************************************************************
	//*	Property Accessors
	//*************************************************************************

	/**
	 * @param int $guidChild
	 * @return $this
	 */
	public function setGuidChild( $guidChild )
	{
		$this->_guidChildChild = $guidChild;
		return $this;
	}

	/**
	 * @return int
	 */
	public function getGuidChild( )
	{
		return $this->_guidChildChild;
	}

	/**
	 * @param boolean $isChild
	 * @return $this
	 */
	public function setIsChild( $isChild )
	{
		$this->_isChild = $isChild;
		return $this;
	}

	/**
	 * @return boolean
	 */
	public function getIsChild( )
	{
		return $this->_isChild;
	}

	/**
	 * @param boolean $isRunning
	 * @return $this
	 */
	public function setIsRunning( $isRunning )
	{
		$this->_isRunning = $isRunning;
		return $this;
	}

	/**
	 * @return boolean
	 */
	public function getIsRunning( )
	{
		return $this->_isRunning;
	}

	/**
	 * @param mixed $jobData
	 * @return $this
	 */
	public function setJobData( $jobData )
	{
		$this->_jobData = $jobData;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getJobData( )
	{
		return $this->_jobData;
	}

	/**
	 * @param float $jobEndTime
	 * @return $this
	 */
	public function setJobEndTime( $jobEndTime )
	{
		$this->_jobEndTime = $jobEndTime;
		return $this;
	}

	/**
	 * @return float
	 */
	public function getJobEndTime( )
	{
		return $this->_jobEndTime;
	}

	/**
	 * @param mixed $jobResult
	 * @return $this
	 */
	public function setJobResult( $jobResult )
	{
		$this->_jobResult = $jobResult;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getJobResult( )
	{
		return $this->_jobResult;
	}

	/**
	 * @param int $jobResultCode
	 * @return $this
	 */
	public function setJobResultCode( $jobResultCode )
	{
		$this->_jobResultCode = $jobResultCode;
		return $this;
	}

	/**
	 * @return int
	 */
	public function getJobResultCode( )
	{
		return $this->_jobResultCode;
	}

	/**
	 * @param float $jobStartTime
	 * @return $this
	 */
	public function setJobStartTime( $jobStartTime )
	{
		$this->_jobStartTime = $jobStartTime;
		return $this;
	}

	/**
	 * @return float
	 */
	public function getJobStartTime( )
	{
		return $this->_jobStartTime;
	}

	/**
	 * @param string $jobStatus
	 * @return $this
	 */
	public function setJobStatus( $jobStatus )
	{
		$this->_jobStatus = $jobStatus;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getJobStatus( )
	{
		return $this->_jobStatus;
	}

	/**
	 * @param int $pidChild
	 * @return $this
	 */
	public function setPidChild( $pidChild )
	{
		$this->_pidChild = $pidChild;
		return $this;
	}

	/**
	 * @return int
	 */
	public function getPidChild( )
	{
		return $this->_pidChild;
	}

	/**
	 * @param int $puidChild
	 * @return $this
	 */
	public function setPuidChild( $puidChild )
	{
		$this->_puidChildChild = $puidChild;
		return $this;
	}

	/**
	 * @return int
	 */
	public function getPuidChild( )
	{
		return $this->_puidChildChild;
	}

	/**
	 * @param boolean $runAsChildProcess
	 * @return $this
	 */
	public function setRunAsChildProcess( $runAsChildProcess )
	{
		$this->_runAsChildProcess = $runAsChildProcess;
		return $this;
	}

	/**
	 * @return boolean
	 */
	public function getRunAsChildProcess( )
	{
		return $this->_runAsChildProcess;
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