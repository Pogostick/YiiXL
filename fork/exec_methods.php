<?php
/**
 * PHP_Fork class usage examples
 * ==================================================================================
 * NOTE: In real world you surely want to keep each class into
 * a separate file, then include() it into your application.
 * For this examples is more useful to keep all_code_into_one_file,
 * so that each example shows a unique feature of the PHP_Fork framework.
 * ==================================================================================
 * exec_methods.php
 * 
 * This example shows a workaround to execute methods into the child process.
 * Always remember that this is an emulation, and the variable spaces are
 * separated between separate processes!
 * There're two kinds of methods: PHP_FORK_VOID_METHOD and PHP_FORK_RETURN_METHOD
 * the first returns nothing, the second can return any serializable value
 * 
 * ATTENTION: this feature of PHP_Fork is highly experimental;
 * all things are OK until we run such an example, that does nothing and simply
 * sleep() all time waiting for a call. Some experiement with real applications
 * seems to show that firing the child process with a signal (that is part of the
 * workaround...) causes the process to stop execution, and then to repeat ALL the
 * run() method after signal caught... This is not an acceptable behaviour and
 * should be tested better.
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
// this is needed as PHP 4.3 in order to use pcntl_signal()
declare (ticks = 1);

/**
 * Classes definition. In real world you surely want to keep each class into
 * a separate file, then include() it into your application.
 * For this examples is more useful to keep all_code_into_one_file
 * 
 * executeThread class inherit from PHP_Fork and must redefine the run() method
 * all the code contained into the run() method will be executed only by the child
 * process; all other methods that you define will be accessible both to the parent
 * and to the child (and will be executed into the relative process)
 */
class executeThread extends PHP_Fork {
    function executeThread($name)
    {
        $this->PHP_Fork($name);
        $GLOBALS["counter"] = 0;
    } 

    function run()
    {
        while (true) {
            $GLOBALS["counter"]++;
            sleep(1);
        } 
    } 
    /**
     * A simple method that can be called from the parent process;
     * There are 2 types of methods, according to the return value
     * 
     * PHP_FORK_VOID_METHOD is a method that return no value; a SINGLE array of parameters (or a single parameter) is expected to be passed
     * PHP_FORK_RETURN_METHOD is a method that return an array to the calling process
     * 
     * if nothing is specified, PHP_FORK_VOID_METHOD behaviour is the default
     */
    function setCounter($val)
    {
        if ($this->_isChild) {
            // do all your stuff here
            // remember that only GLOBAL variables can be accessed with this trick, so
            // if we need to change the value of the variable $counter from the parent into the
            // child (like this code does...) we can't use a class variable ($this->counter),
            // neither a local variable...
            /**
             * * START OF METHOD IMPLEMENTATION *
             */
            $GLOBALS["counter"] = $val[0];
            /**
             * * END OF METHOD IMPLEMENTATION *
             */
        } 
        /**
         * Never change this line, it requires no adjustments...
         */
        else return $this->register_callback_func(func_get_args(), __FUNCTION__);
    } 

    function getCounter($params)
    {
        if ($this->_isChild) {
            return $GLOBALS["counter"];
        } else return $this->register_callback_func(func_get_args(), __FUNCTION__);
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

print "This is the main process.\nPress [X] to terminate, [S] to reset all counters, [G] to get the actual value of counters.\n";

/**
 * Console simple listener
 */
while (true) {
    echo ">";

    $opt = _getInputCLI();
    echo "\n";
    switch ($opt) {
        case "X": 
            // stops all threads
            for ($i = 0;$i < NUM_THREAD;$i++) {
                $executeThread[$i]->stop();
                echo "Stopped " . $executeThread[$i]->getName() . "\n";
            } 
            exit;
            break;

        case "S": 
            // setCounter is a PHP_FORK_VOID_METHOD
            // it only need a paramenter (an array containing data that must be passed to child)
            for ($i = 0;$i < NUM_THREAD;$i++) {
                $executeThread[$i]->setCounter(0);
            } 
            break;

        case "G": 
            // getCounter is a method that reads the value of the thread counter
            // so getCounter is a RETURN_METHOD and MUST be called with 2 parameters;
            // the first is an array of data that must be passed to the method, the second is
            // the constant (RETURN_METHOD)
            for ($i = 0;$i < NUM_THREAD;$i++) {
                echo $executeThread[$i]->getName() . " returns " . $executeThread[$i]->getCounter('', PHP_FORK_RETURN_METHOD) . "\n";
            } 

            break;
    } 
} 

?>
