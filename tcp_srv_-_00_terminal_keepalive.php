<?php
/*
    tms-light - BxBot module
    automated test
    001 - terminal init

*/

include 'functions.php';
include 'config.php';

$testId             = '000';
$testName           = 'application keep-alive test';
$incr_test_number   = 0;
get_banner();

$buf = '';

/* Allow the script to hang around waiting for connections. */
set_time_limit(0);

/* Turn on implicit output flushing so we see what we're getting
 * as it comes in. */
ob_implicit_flush();

if (($sock = socket_create(AF_INET, SOCK_STREAM, 6)) === false) {
    echo "[ERROR] socket_create() failed: reason: " . socket_strerror(socket_last_error()) . "\n";
}

if (!socket_set_option($sock, SOL_SOCKET, SO_REUSEADDR, 1)) {
    echo socket_strerror(socket_last_error($sock));
    exit;
}

if (socket_bind($sock, $address, $port) === false) {
    echo "[ERROR] socket_bind() failed: reason: " . socket_strerror(socket_last_error($sock)) . "\n";
    exit;
}

if (socket_listen($sock, 5) === false) {
    echo "[ERROR] socket_listen() failed: reason: " . socket_strerror(socket_last_error($sock)) . "\n";
}

do {
    if (($msgsock = socket_accept($sock)) === false) {
        echo "[ERROR] socket_accept() failed: reason: " . socket_strerror(socket_last_error($sock)) . "\n";
        break;
    }
    
    /* Send instructions. */
    socket_getpeername($msgsock, $clientIP);

    $connmsg = "[INFO] Got connect from ". $clientIP . " :-)" . " \n";
    //socket_write($msgsock, $connmsg, strlen($msg));
    echo($connmsg);
    sleep(1);
    
    //
    // PAYMENT TERMINAL INITIALIZATION
    //

    

    //
    // send passivate to set up terminal into known state 
    //
    for ($i=1; $i <= 1; $i++) {
        $incr_test_number++;
        echo "[INFO] T81 passivate test begin\n";
        cancel_payment($msgsock);
        $progress_count = 1;
        
        while(strpos($buf, 'B001') !== false) {
            echo "[INFO] Progress count is: $progress_count\n";
            if (false === ($buf = socket_read($msgsock, 2048, PHP_BINARY_READ))) {
                echo "[ERROR] socket_read() failed: reason: " . socket_strerror(socket_last_error($msgsock)) . "\n";
                break 1;
            }
            $progress_count++;
        }
        if (false === ($buf = socket_read($msgsock, 2048, PHP_BINARY_READ))) {
            echo "[ERROR] socket_read() failed: reason: " . socket_strerror(socket_last_error($msgsock)) . "\n";
            break 2;
        }
        if (strpos($buf, 'B201') && (strpos($buf, 'T81') !== false)) {
            echo "[SUCCESS] Test $incr_test_number T81 PASSED :-)\n";

        } else {
            echo "[ERROR] Test $incr_test_number T81 FAILED... \n"; 
            // report and do next test

        }
    }


    //
    // initialize terminal by appinfo command and enable keep-alive
    //
    for ($i=1; $i <= 1; $i++) {
        $incr_test_number++;
        echo "[INFO] T80 AppInfo test begin\n";
        terminal_appinfo($msgsock);

        if (false === ($buf = socket_read($msgsock, 2048, PHP_BINARY_READ))) {
            echo "[ERROR] socket_read() failed: reason: " . socket_strerror(socket_last_error($msgsock)) . "\n";
            break 2;
        }
        if (strpos($buf, 'B201') && (strpos($buf, 'R000') !== false)) {
            echo "[SUCCESS] Test $incr_test_number T80 AppInfo PASSED :-)\n";

        } else {
            echo "[ERROR] Test $incr_test_number T80 AppInfo FAILED... \n"; 
            // report and do next test

        }

        do {
            if (false === ($buf = socket_read($msgsock, 2048, PHP_BINARY_READ))) {
                echo "[ERROR] socket_read() failed: reason: " . socket_strerror(socket_last_error($msgsock)) . "\n";
                break 2;
                }  
                handle_keepalive($buf,$msgsock);
        }
        while(true);
        socket_close($msgsock);
 


    }
    echo "[INFO] ALL TESTS DONE\n";
    continue;

    }
    while(true);
    echo "program end";
    socket_close($msgsock);
?>