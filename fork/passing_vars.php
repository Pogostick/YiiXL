<?php
/**
 * PHP_Fork class usage examples
 * ==================================================================================
 * NOTE: In real world you surely want to keep each class into
 * a separate file, then include() it into your application.
 * For this examples is more useful to keep all_code_into_one_file,
 * so that each example shows a unique feature of the PHP_Fork framework.
 * ==================================================================================
 * passing_vars.php
 * 
 * This example shows variable exchange between the parent process
 * and started pseudo-threads. This was not possible in previous releases because
 * parent and child processes lives into different memory spaces,
 * they are separate processes with their own PID and not separate instances living
 * into the same JVM; this is just forking, not real threading...
 * This framework offers a workaround based Shared Memory usage.
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
// Class definition; as into previos example (basic.php), this class simply
// increment a counter each second; instead of printing the value to stdout,
// we'll store it into an accessible location for latter use.
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
            // by parent process (calling the getVariable() method)
            $this->setVariable('counter', $this->counter++);
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
// Main program. Bring up NUM_THREAD instances of the executeThread class that
// runs concurrently. It's a multi-thread app with a few lines of code!!!
// Into this example we have a console to control thread behaviour and test
// their counter value.
for ($i = 0;$i < NUM_THREAD;$i++) {
    $executeThread[$i] = new executeThread ("executeThread-" . $i);
    $executeThread[$i]->start();
    echo "Started " . $executeThread[$i]->getName() . " with PID " . $executeThread[$i]->getPid() . "...\n";
} 

echo "This is the main process.\nPress [X] to terminate, [G] to read pseudo-thread's counter.\n";
// Console simple listener
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
        case "G":
            for ($i = 0;$i < NUM_THREAD;$i++) {
                echo $executeThread[$i]->getName() . " returns " . $executeThread[$i]->getCounter() . "\n";
            } 
            break;
    } 
} 
// Functions used by the console
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

?>
