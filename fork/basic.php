<?php
/**
 * PHP_Fork class usage examples
 * ==================================================================================
 * NOTE: In real world you surely want to keep each class into
 * a separate file, then include() it into your application.
 * For this examples is more useful to keep all_code_into_one_file,
 * so that each example shows a unique feature of the PHP_Fork framework.
 * ==================================================================================
 * basic.php
 * 
 * This is the most basic example I can think about...
 * Simply increment an internal counter every second, printing it to
 * stdout. Repeat this 10 times, then exit. The class extends PHP_Fork, so we have
 * multiple instances running cuncurrently, as shown by the timestamp of each thread.
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
// Class definition; this class have a very basic purpose,, it simply
// has an inernal counter and increment it every each second...
class executeThread extends PHP_Fork {
    var $counter;

    function executeThread($name)
    {
        $this->PHP_Fork($name);
        $this->counter = 0;
    } 

    function run()
    {
        $i = 0;
        while ($i < 10) {
            print time() . "-(" . $this->getName() . ")-" . $this->counter++ . "\n";
            sleep(1);
            $i++;
        } 
    } 
} 
// Main program. Bring up NUM_THREAD instances of the executeThread class that
// runs concurrently. It's a multi-thread app with a few lines of code!!!
for ($i = 0;$i < NUM_THREAD;$i++) {
    $executeThread[$i] = new executeThread ("executeThread-" . $i);
    $executeThread[$i]->start();
    echo "Started " . $executeThread[$i]->getName() . " with PID " . $executeThread[$i]->getPid() . "...\n";
} 

echo "\nThis is the main process.\nNothing to do, so exit...\n";

?>
