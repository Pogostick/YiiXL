<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker: */
// +----------------------------------------------------------------------+
// | PHP Version 4                                                        |
// +----------------------------------------------------------------------+
// | Copyright (c) 1997-2003 The PHP Group                                |
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.02 of the PHP license,      |
// | that is bundled with this package in the file LICENSE, and is        |
// | available at through the world-wide-web at                           |
// | http://www.php.net/license/2_02.txt.                                 |
// | If you did not receive a copy of the PHP license and are unable to   |
// | obtain it through the world-wide-web, please send a note to          |
// | license@php.net so we can mail you a copy immediately.               |
// +----------------------------------------------------------------------+
// | Author: Luca Mariano <luca.mariano@email.it>                         |
// +----------------------------------------------------------------------+
// $Id: Fork.php
/**
 * Constant values to set type of method call we want to emulate.
 *
 * When calling a pseudo-thread method we try to emulate the behaviour of a real thread;
 * we need to know if the method to emulate can return any value or is a void method.
 */
define ('PHP_FORK_VOID_METHOD', -1);
define ('PHP_FORK_RETURN_METHOD', -2);

/**
 * PHP_Fork class. Wrapper around the pcntl_fork() stuff
 * with a API set like Java language.
 * Practical usage is done by extending this class, and re-defining
 * the run() method.
 * Example:
 * <code>
 *     class executeThread extends PHP_Fork {
 *        var $counter;
 *
 *        function executeThread($name)
 *        {
 *            $this->PHP_Fork($name);
 *            $this->counter = 0;
 *        }
 *
 *        function run()
 *        {
 *            $i = 0;
 *            while ($i < 10) {
 *                print time() . "-(" . $this->getName() . ")-" . $this->counter++ . "\n";
 *                sleep(1);
 *                $i++;
 *            }
 *        }
 * }
 * </code>
 *
 * This way PHP developers can enclose logic into a class that extends
 * PHP_Fork, then execute the start() method that forks a child process.
 * Communications with the forked process is ensured by using a Shared Memory
 * Segment; by using a user-defined signal and this shared memory developers
 * can access to child process methods that returns a serializable variable.
 *
 * The shared variable space can be accessed with the tho methods:
 *
 * o void setVariable($name, $value)
 * o mixed getVariable($name)
 *
 * $name must be a valid PHP variable name;
 * $value must be a variable or a serializable object.
 * Resources (db connections, streams, etc.) cannot be serialized and so they're not correctly handled.
 *
 * Requires PHP build with --enable-cli --with-pcntl --enable-shmop.<br>
 * Only runs on *NIX systems, because Windows lacks of the pcntl ext.
 *
 * @example action_dispatcher.php shows a multiprocess application scheme where all processes run a sleep() cycle, and a centralized dispatcher pass them the work.
 * @example simple_controller.php shows how to attach a controller to started pseudo-threads.
 * @example exec_methods.php shows a workaround to execute methods into the child process.
 * @example passing_vars.php shows variable exchange between the parent process and started pseudo-threads.
 * @example basic.php a basic example, only two pseudo-threads that increment a counter simultaneously.
 * @author Luca Mariano <luca.mariano@email.it>
 * @version 1.1
 * @package PHP::Fork
 */
class PHP_Fork {
    /**
     * The pseudo-thread name: must be unique between PHP processes
     *
     * @var string
     * @access private
     */
    var $_name;

    /**
     * PID of the child process.
     *
     * @var integer
     * @access private
     */
    var $_pid;

    /**
     * PUID of the child process owner; if you want to set this you must create and
     * start() the pseudo-thread as root.
     *
     * @var integer
     * @access private
     */
    var $_puid;

    /**
     * GUID of the child process owner; if you want to set this you must create and
     * start() the pseudo-thread as root.
     *
     * @var integer
     * @access private
     */
    var $_guid;

    /**
     * Are we into the child process?
     *
     * @var boolean
     * @access private
     */
    var $_isChild;

    /**
     * A data structure to hold data for Inter Process Communications
     *
     * It's an associative array, some keys are reserved and cannot be used:
     * _call_method, _call_input, _call_output, _call_type, _pingTime;
     *
     * @var array
     * @access private
     */
    var $_internal_ipc_array;

    /**
     * KEY to access to Shared Memory Area.
     *
     * @var integer
     * @access private
     */
    var $_internal_ipc_key;

    /**
     * KEY to access to Sync Semaphore.
     *
     * The semaphore is emulated with a boolean stored into a
     * shared memory segment, because we don't want to add sem_*
     * support to PHP interpreter.
     *
     * @var integer
     * @access private
     */
    var $_internal_sem_key;

    /**
     * Is Shared Memory Area OK? If not, the start() method will block
     * otherwise we'll have a running child without any communication channel.
     *
     * @var boolean
     * @access private
     */
    var $_ipc_is_ok;

    /**
     * Whether the process is yet forked or not
     *
     * @var boolean
     * @access private
     */
    var $_running;

    /**
     * Pointer to file for ftok()
     *
     * @var string
     * @access private
     */
    var $_ipc_file_1;

    /**
     * Pointer to file for ftok()
     *
     * @var string
     * @access private
     */
    var $_ipc_file_2;

    /**
     * PHP_Fork::PHP_Fork()
     * Allocates a new pseudo-thread object and set its name to $name.
     * Optionally, set a PUID, a GUID and a UMASK for the child process.
     * This also initialize Shared Memory Segments for process communications.
     *
     * Supposing that you've defined the executeThread class as above,
     * creating the pseudo-threads instances is very simple:
     *
     * <code>
     * 	...
     *       $executeThread1 = new executeThread ("executeThread-1");
     *       $executeThread2 = new executeThread ("executeThread-2");
     *     ...
     * </code>
     * The pseudo-thread name must be unique; you can create and start as many pseudo-threads as you want;
     * the limit is (of course) system resources.
     *
     * @param string $name The name of this pseudo-thread; must be unique between PHP processes running on the same server.
     * @param integer $puid Owner of the forked process; if none, the user will be the same that created the pseudo-thread
     * @param integer $guid Group of the forked process; if none, the group will be the same of the user that created the pseudo-thread
     * @param integer $umask the umask of the new process; if none, the umask will be the same of the user  that created the pseudo-thread
     * @access public
     * @return bool true if the Shared Memory Segments are OK, false otherwise.<br>Notice that only if shared mem is ok the process will be forked.
     */
    function PHP_Fork($name, $puid = 0, $guid = 0, $umask = -1)
    {
        $this->_running = false;

        $this->_name = $name;
        $this->_guid = $guid;
        $this->_puid = $puid;

        if ($umask != -1) umask($umask);

        $this->_isChild = false;
        $this->_internal_ipc_array = array();
        // try to create the shared memory segment
        // the variable $this->_ipc_is_ok contains the return code of this
        // operation and MUST be checked before forking
        if ($this->_createIPCsegment() && $this->_createIPCsemaphore())
            $this->_ipc_is_ok = true;
        else
            $this->_ipc_is_ok = false;
    }

    /**
     * PHP_Fork::isRunning()
     * Test if the pseudo-thread is already started.
     * A pseudo-thread that is instantiated but not started only exist as an instance of
     * PHP_Fork class into parent process; no forking is done until the start() method
     * is called.
     *
     * @return boolean true is the child is already forked.
     */
    function isRunning()
    {
        if ($this->_running)
            return true;
        else
            return false;
    }

    /**
     * PHP_Fork::setVariable()
     *
     * Set a variable into the shared memory segment so that it can accessed
     * both from the parent & from the child process.
     *
     * @see PHP_Fork::getVariable()
     */
    function setVariable($name, $value)
    {
        $this->_internal_ipc_array[$name] = $value;
        $this->_writeToIPCsegment();
    }

    /**
     * PHP_Fork::getVariable()
     *
     * Get a variable from the shared memory segment
     *
     * @see PHP_Fork::setVariable()
     * @return mixed the requested variable (or NULL if it doesn't exists).
     */
    function getVariable($name)
    {
        $this->_readFromIPCsegment();
        return $this->_internal_ipc_array[$name];
    }

    /**
     * PHP_Fork::setAlive()
     *
     * Set a pseudo-thread property that can be read from parent process
     * in order to know the child activity.
     *
     * Practical usage requires that child process calls this method at regular
     * time intervals; parent will use the getLastAlive() method to know
     * the elapsed time since the last pseudo-thread life signals...
     *
     * @see PHP_Fork::getLastAlive()
     */
    function setAlive()
    {
        $this->setVariable('_pingTime', time());
    }

    /**
     * PHP_Fork::getLastAlive()
     *
     * Read the time elapsed since the last child setAlive() call.
     *
     * This method is useful because often we have a pseudo-thread pool and we
     * need to know each pseudo-thread status.
     * if the child executes the setAlive() method, the parent with
     * getLastAlive() can know that child is alive.
     *
     * @see PHP_Fork::setAlive()
     * @return integer the elapsed seconds since the last child setAlive() call.
     */
    function getLastAlive()
    {
        $timestamp = intval($this->getVariable('_pingTime'));
        if ($timestamp == 0)
            return 0;
        else
            return (time() - $timestamp);
    }

    /**
     * PHP_Fork::getName()
     * Returns this pseudo-thread's name.
     *
     * @see PHP_Fork::setName()
     * @return string the name of the pseudo-thread.
     */

    function getName()
    {
        return $this->_name;
    }

    /**
     * PHP_Fork::getPid()
     * Return the PID of the current pseudo-thread.
     *
     * @return integer the PID.
     */

    function getPid()
    {
        return $this->_pid;
    }

    /**
     * PHP_Fork::register_callback_func()
     *
     * This is called from within the parent method; all the communication stuff is done here...
     *
     * @example exec_methods.php
     * @param  $arglist
     * @param  $methodname
     */
    function register_callback_func($arglist, $methodname)
    {
        // this is the parent, so we really cannot execute the method...
        // check arguments passed to the method...
        if (is_array($arglist) && count ($arglist) > 1) {
            if ($arglist[1] == PHP_FORK_RETURN_METHOD)
                $this->_internal_ipc_array['_call_type'] = PHP_FORK_RETURN_METHOD;
            else
                $this->_internal_ipc_array['_call_type'] = PHP_FORK_VOID_METHOD;
        } else $this->_internal_ipc_array['_call_type'] = PHP_FORK_VOID_METHOD;
        // These setting are common to both the calling types
        $this->_internal_ipc_array['_call_method'] = $methodname; // '_call_method' is the name of the called method
        $this->_internal_ipc_array['_call_input'] = $arglist; // '_call_input' is the input array

        // Write the IPC data to the shared segment
        $this->_writeToIPCsegment();
        // Now we need to differentiate a bit...
        switch ($this->_internal_ipc_array['_call_type']) {
            case PHP_FORK_VOID_METHOD:
                // notify the child so it can process the request
                $this->_sendSigUsr1();
                break;

            case PHP_FORK_RETURN_METHOD:
                // pseudo-semaphore +-+-+
                // +0+0+
                // +-+-+
                shmop_write($this->_internal_sem_key, 1, 0);
                // pseudo-semaphore +-+-+
                // +1+0+
                // +-+-+

                // notify the child so it can process the request
                $this->_sendSigUsr1();
                // blocks until the child process return
                $this->_waitIPCSemaphore();
                // read from the SHM segment...
                // the result is stored into $this->_internal_ipc_key['_call_output']
                $this->_readFromIPCsegment();
                // ok, data are returned back. Now we can reset the semaphore
                // pseudo-semaphore +-+-+
                // +0+1+
                // +-+-+
                shmop_write($this->_internal_sem_key, 0, 1);
                // pseudo-semaphore +-+-+
                // +0+0+
                // +-+-+

                // now return the result...
                return $this->_internal_ipc_array['_call_output'];
                break;
        }
    }

    /**
     * PHP_Fork::run()
     *
     * This method actually implements the pseudo-thread logic.<BR>
     * Subclasses of PHP_Fork MUST override this method as v.0.2
     *
     * @abstract
     */

    function run()
    {
        die ("Fatal error: PHP_Fork class cannot be run by itself!\nPlease extend it and override the run() method");
    }

    /**
     * PHP_Fork::setName()
     * Changes the name of this thread to the given name.
     *
     * @see PHP_Fork::getName()
     * @param  $name
     */

    function setName($name)
    {
        $this->_name = $name;
    }

    /**
     * PHP_Fork::start()
     * Causes this pseudo-thread to begin parallel execution.
     *
     * <code>
     * 	...
     *       $executeThread1->start();
     *     ...
     * </code>
     *
     * This method check first of all the Shared Memory Segment; if ok, if forks
     * the child process, attach signal handler and returns immediatly.
     * The status is set to running, and a PID is assigned.
     * The result is that two pseudo-threads are running concurrently: the current thread (which returns from the call to the start() method) and the other thread (which executes its run() method).
     */

    function start()
    {
        if (!$this->_ipc_is_ok) {
            die ('Fatal error, unable to create SHM segments for process communications');
        }

        pcntl_signal(SIGCHLD, SIG_IGN);

        $pid = pcntl_fork();
        if ($pid == 0) {
            // this is the child
            $this->_isChild = true;
            sleep(1);
            // install the signal handler
            pcntl_signal(SIGUSR1, array($this, "_sig_handler"));
            // if requested, change process identity
            if ($this->_guid != 0)
                posix_setgid($this->_guid);

            if ($this->_puid != 0)
                posix_setuid($this->_puid);

            $this->run();
            // Added 21/Oct/2003: destroy the child after run() execution
            // needed to avoid unuseful child processes after execution
            exit(0);
        } else {
            // this is the parent
            $this->_isChild = false;
            $this->_running = true;
            $this->_pid = $pid;
        }
    }

    /**
     * PHP_Fork::stop()
     * Causes the current thread to die.
     *
     *
     * <code>
     * 	...
     *       $executeThread1->stop();
     *     ...
     * </code>
     *
     * The relative process is killed and disappears immediately from the processes list.
     *
     * @return boolean true if the process is succesfully stopped, false otherwise.
     */

    function stop()
    {
        $success = false;

        if ($this->_pid > 0) {
            posix_kill ($this->_pid, 9);
            pcntl_waitpid ($this->_pid, $temp = 0, WNOHANG);
            $success = pcntl_wifexited ($temp) ;
            $this->_cleanThreadContext();
        }

        return $success;
    }
    // PRIVATE METHODS BEGIN
    /**
     * PHP_Fork::_cleanThreadContext()
     *
     * Internal method: destroy thread context and free relative resources.
     *
     * @access private
     */

    function _cleanThreadContext()
    {
        @shmop_delete($this->_internal_ipc_key);
        @shmop_delete($this->_internal_sem_key);

        @shmop_close($this->_internal_ipc_key);
        @shmop_close($this->_internal_sem_key);

        unlink ($this->_ipc_file_1);
        unlink ($this->_ipc_file_2);

        $this->_running = false;
        unset($this->_pid);
    }

    /**
     * PHP_Fork::_sig_handler()
     *
     * This is the signal handler that make the communications between client and server possible.<BR>
     * DO NOT override this method, otherwise the thread system will stop working...
     *
     * @param  $signo
     * @access private
     */
    function _sig_handler($signo)
    {
        switch ($signo) {
            case SIGTERM:
                // handle shutdown tasks
                exit;
                break;
            case SIGHUP:
                // handle restart tasks
                break;
            case SIGUSR1:
                // this is the User-defined signal we'll use.
                // read the SHM segment...
                $this->_readFromIPCsegment();

                $method = $this->_internal_ipc_array['_call_method'];
                $params = $this->_internal_ipc_array['_call_input'];

                switch ($this->_internal_ipc_array['_call_type']) {
                    case PHP_FORK_VOID_METHOD:
                        // simple call the (void) method and return immediatly
                        // no semaphore is placed into parent, so the processing is async
                        $this->$method($params);
                        break;

                    case PHP_FORK_RETURN_METHOD:
                        // process the request...
                        $this->_internal_ipc_array['_call_output'] = $this->$method($params);
                        // write the result into IPC segment
                        $this->_writeToIPCsegment();
                        // unlock the semaphore but blocks _writeToIPCsegment();
                        // pseudo-semaphore +-+-+
                        // +1+0+
                        // +-+-+
                        shmop_write($this->_internal_sem_key, 0, 0);
                        shmop_write($this->_internal_sem_key, 1, 1);
                        // pseudo-semaphore +-+-+
                        // +0+1+
                        // +-+-+
                        break;
                }
                break;
            default:
                // handle all other signals
        }
    }

    /**
     * PHP_Fork::_sendSigUsr1()
     *
     * Sends signal to the child process
     *
     * @access private
     */
    function _sendSigUsr1()
    {
        if ($this->_pid > 0)
            posix_kill ($this->_pid, SIGUSR1);
    }

    /**
     * PHP_Fork::_waitIPCSemaphore()
     *
     * @access private
     */
    function _waitIPCSemaphore()
    {
        while (true) {
            $ok = shmop_read($this->_internal_sem_key, 0, 1);

            if ($ok == 0)
                break;
            else
                usleep(10);
        }
    }

    /**
     * PHP_Fork::_readFromIPCsegment()
     *
     * @access private
     */
    function _readFromIPCsegment()
    {
        $serialized_IPC_array = shmop_read($this->_internal_ipc_key, 0, shmop_size($this->_internal_ipc_key));

        if (!$serialized_IPC_array)
            print("Fatal exception reading SHM segment (shmop_read)\n");
        // shmop_delete($this->_internal_ipc_key);
        unset($this->_internal_ipc_array);
        $this->_internal_ipc_array = @unserialize($serialized_IPC_array);
    }

    /**
     * PHP_Fork::_writeToIPCsegment()
     *
     * @access private
     */
    function _writeToIPCsegment()
    {
        // read the transaction bit (2° bit of _internal_sem_key segment)
        // if it value is 1 we're into the execution of a PHP_FORK_RETURN_METHOD
        // so we must NOT write to segment (data corruption...)
        if (shmop_read($this->_internal_sem_key, 1, 1) == 1)
            return;

        $serialized_IPC_array = serialize($this->_internal_ipc_array);
        // set the exchange array (IPC) into the shared segment
        $shm_bytes_written = shmop_write($this->_internal_ipc_key, $serialized_IPC_array, 0);
        // check if lenght of SHM segment is enougth to contain data...
        if ($shm_bytes_written != strlen($serialized_IPC_array))
            die("Fatal exception writing SHM segment (shmop_write)" . strlen($serialized_IPC_array) . "-" . shmop_size($this->_internal_ipc_key));
    }

    /**
     * PHP_Fork::_createIPCsegment()
     *
     * @return boolean true if the operation succeeded, false otherwise.
     * @access private
     */
    function _createIPCsegment()
    {
        $this->_ipc_file_1 = "/tmp/" . rand() . md5($this->getName()) . ".shm";
        touch ($this->_ipc_file_1);
        $shm_key = ftok($this->_ipc_file_1, 't');
        if ($shm_key == -1)
            die ("Fatal exception creating SHM segment (ftok)");

        $this->_internal_ipc_key = @shmop_open($shm_key, "c", 0644, 10240);
        if (!$this->_internal_ipc_key) {
            return false;
        }
        return true;
    }

    /**
     * PHP_Fork::_createIPCsemaphore()
     *
     * @return boolean true if the operation succeeded, false otherwise.
     * @access private
     */
    function _createIPCsemaphore()
    {
        $this->_ipc_file_2 = "/tmp/" . rand() . md5($this->getName()) . ".sem";
        touch ($this->_ipc_file_2);
        $sem_key = ftok($this->_ipc_file_2, 't');
        if ($sem_key == -1)
            die ("Fatal exception creating semaphore (ftok)");
        $this->_internal_sem_key = shmop_open($sem_key, "c", 0644, 10);
        if (!$this->_internal_sem_key) {
            return false;
        }
        return true;
    }
}
