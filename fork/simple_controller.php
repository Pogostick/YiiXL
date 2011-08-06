<?php
/**
 * PHP_Fork class usage examples
 * ==================================================================================
 * NOTE: In real world you surely want to keep each class into
 * a separate file, then include() it into your application.
 * For this examples is more useful to keep all_code_into_one_file,
 * so that each example shows a unique feature of the PHP_Fork framework.
 * ==================================================================================
 * simple_controller.php
 *
 * This example shows how to attach a controller to started pseudo-threads
 * Forked processes can sometime die or defunct for many reasons...
 * Without a controller process that periodically check thread status we
 * can not ensure that started threads are still alive into system
 * Here we have n executeThreads and a controllerThread that check all them
 * when the controller detect that a thread is died it try to respawn it.
 *
 * ATTENTION: this example often fails on Cygwin platform, probably due to some bugs
 * into ipc implementations that prevent the shared memory to be totally released
 * when a process die.
 *
 * ==================================================================================
 *
 * Copyright (c) 2003-2002 by Luca Mariano (luca.mariano@email.it)
 * http://www.lucamariano.it
 *
 * This program is free software. You can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License.
 */
// Import of base class
require_once ("../Fork.php");
// number of executeThreads we want
define ("NUM_THREAD", 2);
// max delay (seconds) that controller can accept from child's ping
define ("CTRL_MAX_IDLE", 3);
// controller will check threads status every CTRL_POLLING_INTERVAL secs.
define ("CTRL_POLLING_INTERVAL", 5);
// The same as previos examples, with the add of the controller ping...
class executeThread extends PHP_Fork {
    var $counter;

    function executeThread($name)
    {
        $this->PHP_Fork($name);
        $this->counter = 0;
    }

    function run()
    {
        while (true) {
            // setVariable is a method inherited from PHP_Fork super class
            // it sets a variable that can be accessed thru its name
            // by parent process
            $this->setVariable('counter', $this->counter++);
            /**
             * While using the controller, every child must notify to controller that it's alive
             * The frequency of this notify must be set according to the parameter $maxIdleTime
             * (controllerThread constructor, default is 60 sec.)
             * Typically executeThreads runs an infinite loop making something useful; you should
             * calculate a reasonable time for one loop, then set the controller's $maxIdleTime
             * to a superior value.
             * Into the while(true) { } loop of the thread insert a call to the "setAlive" method
             * so that controller can detect properly the status of child processes.
             */
            $this->setAlive();
            sleep(1);
        }
    }

    function getCounter()
    {
        // parent process can call this facility method
        // in order to get back the actual value of the counter
        return $this->getVariable('counter');
    }
}

class controllerThread extends PHP_Fork {
    var $_sleepInt;
    var $_executeThreadPool;
    var $_maxIdleTime;
    var $_respawnThread;

    /**
     * controllerThread::controllerThread()
     *
     * @param  $name
     * @param  $executeThreadPool
     * @param  $maxIdleTime
     * @param  $interval
     * @return
     */
    function controllerThread($name, &$executeThreadPool, $maxIdleTime = 60, $interval = 60)
    {
        $this->PHP_Fork($name);
        $this->_sleepInt = $interval;
        $this->_executeThreadPool = &$executeThreadPool;
        $this->_maxIdleTime = $maxIdleTime;
        $this->_respawnThread = array();

        $this->setVariable('_executeThreadPool', $this->_executeThreadPool);
    }

    function run()
    {
        while (true) {
            $this->_detectDeadChilds();
            $this->_respawnDeadChilds();

            sleep($this->_sleepInt);
        }
    }
    function stopAllThreads()
    {
        $this->_executeThreadPool = $this->getThreadPool();
        foreach($this->_executeThreadPool as $thread) {
            $thread->stop();
            echo "Stopped " . $thread->getName() . "\n";
        }
        unset($this->_executeThreadPool);
        $this->_executeThreadPool = array();
        $this->setVariable('_executeThreadPool', $this->_executeThreadPool);
    }

    function getThreadPool()
    {
        return $this->getVariable('_executeThreadPool');
    }


    function _detectDeadChilds()
    {
        // check every executethread to see if it is alive...
        foreach ($this->_executeThreadPool as $idx => $executeThread) {
            if ($executeThread->getLastAlive() > $this->_maxIdleTime) {
                // this thread is not responding, probably [defunct]
                $threadName = $executeThread->getName();
                print time() . "-" . $this->getName() . "-" . $threadName . " seems to be died...\n";
                // so let's kill it...
                $executeThread->stop();

                unset($executeThread);
                // remove this thread from the pool
                array_splice($this->_executeThreadPool, $idx, 1);
                // and add them to the "to be respawned" thread list...
                $this->_respawnThread[] = $threadName;
                // stop this foreach
                break;
            }
        }
    }

    function _respawnDeadChilds()
    {
        foreach ($this->_respawnThread as $idx => $threadName) {
            $n = &new executeThread ($threadName);
            // usually we try to start a Thread without this check
            // if Shared Memory Area is not ready, the start() method
            // die, so the process is destroyed. When respawing a dead child
            // this is not useful, because die() will cause the controller itself
            // to die!
            // So let's check if IPC is ok before call the start() method.
            if ($n->_ipc_is_ok) {
                $n->start();
                $this->_executeThreadPool[] = &$n;
                print time() . "-" . $this->getName() . "- New instance of " . $threadName . " successfully spawned (PID=" . $n->getPid() . ")\n";
                array_splice($this->_respawnThread, $idx, 1);
                $this->setVariable('_executeThreadPool', $this->_executeThreadPool);
            } else {
                print time() . "-" . $this->getName() . "-" . "Unable to create IPC segment...\n";
            }
        }
    }
}

/**
 * Functions used by the console
 */
function _getInputCLI()
{
    $opt = _read();
    $opt = strtoupper (trim($opt));
    return $opt;
}

function _read()
{
    $fp = fopen("php://stdin", "r");
    $input = fgets($fp, 255);
    fclose($fp);

    return $input;
}

/**
 * Main program. Bring up two instances of the executeThread class that
 * runs concurrently. It's a multi-thread app with a few lines of code!!!
 * executeThread does nothing interesting, it simply has a counter and increment
 * this counter each second... (see class definition at top of this file)
 */
for ($i = 0;$i < NUM_THREAD;$i++) {
    $executeThread[$i] = &new executeThread ("executeThread-" . $i);
    $executeThread[$i]->start();
    echo "Started " . $executeThread[$i]->getName() . " with PID " . $executeThread[$i]->getPid() . "...\n";
}

$ctrl = new controllerThread("controllerThread", $executeThread, CTRL_MAX_IDLE, CTRL_POLLING_INTERVAL);
$ctrl->start();
print "Started " . $ctrl->getName() . " with PID " . $ctrl->getPid() . "...\n\n";

echo "This is the main process.\nPress [X] to terminate, [G] to read thread's counter.\nTo test the controller respawn functionality, simply kill one (or more) executeThread (kill -9 [pid]). The controller should detect the died child & respawn it very soon...\n";

/**
 * Console simple listener
 */
while (true) {
    echo ">";

    $opt = _getInputCLI();
    echo "\n";
    switch ($opt) {
        case "X":
            // when using the controller we cannot traverse the $executeThread array
            // because it only contains the original pool of thread.
            // if a thread dies, the controller respawn it and updates it's own
            // thread pool. The method stopAllThreads() stops all running threads.
            $ctrl->stopAllThreads();
            // stop the controller itself
            $ctrl->stop();
            print "Stopped " . $ctrl->getName() . "\n";
            exit;
            break;

        case "G":
            	$pool = $ctrl->getThreadPool();
            	foreach ($pool as $thread) {
            		//print_r($thread);
                	echo $thread->getName() . " returns " . $thread->getCounter() . "\n";
                	}
            break;

    }
}

?>
