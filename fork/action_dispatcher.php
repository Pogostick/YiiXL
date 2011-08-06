<?php
/**
 * PHP_Fork class usage examples
 * ==================================================================================
 * NOTE: In real world you surely want to keep each class into
 * a separate file, then include() it into your application.
 * For this examples is more useful to keep all_code_into_one_file,
 * so that each example shows a unique feature of the PHP_Fork framework.
 * ==================================================================================
 * action_dispatcher.php
 *
 * This example shows a multi-process application model built using PHP_Fork.
 * Execute-threads makes the work, but they don't use the infinite cycle run();
 * work are passed from the parent (console) to pseudo-thread using a remote
 * method call (PHP_FORK_VOID_METHOD), simulating the behaviour of a centralized
 * calls dispatcher.
 *
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
// Import of PEAR Mail class
require_once ("Mail.php");
// number of executeThreads we want
define ("NUM_THREAD", 10);
// SMTP host to use for mailing
define ("SMTP_TEST_HOST", "smtp.consulenti.csi.it");
// The example will send a lot of mails to this address, so keep sure it's one of yours...
define ("MAIL_RECIPIENT", "luca.mariano@consulenti.csi.it");
// this is needed as PHP 4.3 in order to use pcntl_signal()
declare (ticks = 1);

/**
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
    }

    function run()
    {
    	// this thread sits sleeping until the dispatcher pass some work to it...
        while (true) {
            sleep(60);
        }
    }

    function sendMail($msgObj)
    {
        if ($this->_isChild) {
            $msgObj = &$msgObj[0];
            // to have a feedback of pseudo-thread cuncurrency level
            $msgObj['subject'] .= " posted at " . getmicrotime() . " from " . $this->getName();

            srand((double)microtime() * 1000000);

            $params["host"] = "smtp.consulenti.csi.it";

            $mail = &Mail::factory ("smtp", $params);

            $headers['From'] = $msgObj['from'];
            $headers['To'] = $msgObj['to'];
            $headers['Subject'] = $msgObj['subject'] . "(" . rand(0, 100000) . ")" ;

            $mail->send($msgObj['to'], $headers, $msgObj['body'] . "\n" . rand(0, 100000));
        }
        /**
         * Never change this line, it requires no adjustments...
         */
        else return $this->register_callback_func(func_get_args(), __FUNCTION__);
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

function getmicrotime()
{
    list($usec, $sec) = explode(" ", microtime());
    return ((float)$usec + (float)$sec);
}
/**
 * Main program. Bring up n instances of the executeThread class that
 * runs concurrently. It's a multi-thread app with a few lines of code!!!
 */
for ($i = 0;$i < NUM_THREAD;$i++) {
    $executeThread[$i] = &new executeThread ("executeThread-" . $i);
    $executeThread[$i]->start();
    echo "Started " . $executeThread[$i]->getName() . " with PID " . $executeThread[$i]->getPid() . "...\n";
}

print "This is the main process.\nPress [X] to terminate, [S] tells executeThreads to send " . NUM_THREAD . " mail msgs\n";

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

			// compose the mail; here data are fixed, in a real application
			// data are read from a DB table (or a socket, a named pipe, etc)
            $msgObj = array();
            $msgObj['to'] = MAIL_RECIPIENT;
            $msgObj['from'] = "foo@foo.it";
            $msgObj['subject'] = "PHP_Fork test ";
            $msgObj['body'] = "Main program created this message at " . getmicrotime();

            echo "Sending mail to " . NUM_THREAD . " addresses...\n";
            $time_start = getmicrotime();

			// tells all running processes to send the same mail
			// in real word we should test if the the process is busy;
			// if not, we'll pass to it a $msgObj to process, then we'll remove
			// the $msgObj from message queue.
            for ($i = 0;$i < NUM_THREAD;$i++) {
                $executeThread[$i]->sendMail($msgObj);
            }
            $time_end = getmicrotime();
            $time = $time_end - $time_start;
            echo "Done! Elapsed " . $time . ", avg " . (($time / NUM_THREAD) * 1000) . " msecs/msg \n";
            break;
    }
}

?>
