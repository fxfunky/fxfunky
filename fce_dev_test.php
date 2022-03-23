<?php

include 'functions.php';
include 'config.php';

// test case settings
$testId     = '001';
$testName   = 'terminal init';
$testResult = 1;

get_banner();


/* Allow the script to hang around waiting for connections. */
set_time_limit(0);

/* Turn on implicit output flushing so we see what we're getting
 * as it comes in. */
ob_implicit_flush();



if (($msgsock = socket_create(AF_INET, SOCK_STREAM, 6)) === false) {
    echo "[ERROR] socket_create() failed: reason: " . socket_strerror(socket_last_error()) . "\n";
}

if (!socket_set_option($msgsock, SOL_SOCKET, SO_REUSEADDR, 1)) {
    echo socket_strerror(socket_last_error($msgsock));
    exit;
}

if (socket_bind($msgsock, $address, $port) === false) {
    echo "[ERROR] socket_bind() failed: reason: " . socket_strerror(socket_last_error($msgsock)) . "\n";
    exit;
}

if (socket_listen($msgsock, 5) === false) {
    echo "[ERROR] socket_listen() failed: reason: " . socket_strerror(socket_last_error($msgsock)) . "\n";
}

do {
    
    /* Send instructions. */
    socket_getpeername($msgsock, $clientIP);

    $connmsg = "[INFO] Got connect from ". $clientIP . " :-)" . " \n";
    //socket_write($msgsock, $connmsg, strlen($msg));
    echo($connmsg);
    sleep(0.1);
    
    //
    // PAYMENT TERMINAL INITIALIZATION
    //

    // initialize terminal by appinfo command and enable keep-alive
    $bprotcmd = hex2bin('024231303120202020202020203232303331313232313934323230303030303034413541351c54383003');
    socket_write($msgsock, $bprotcmd, strlen($bprotcmd));
    sleep(1);

    do {
        if (false === ($buf = socket_read($msgsock, 2048, PHP_BINARY_READ))) {
            echo "[ERROR] socket_read() failed: reason: " . socket_strerror(socket_last_error($msgsock)) . "\n";
            break 2;
        }

        handle_keepalive($buf, $msgsock);

        if (strpos($buf, 'B201') !== false) {
            echo "[INFO] B2 Received\n";
            $testResult = 0;


            result_dbinsert($dbHost, $dbUser);
            //result_dbinsert($dbHost, $dbUser, $testId, $testName, $clientIP, $testResult);
        }

        $talkback = $buf;
        socket_write($msgsock, $talkback, strlen($talkback));
        echo "[INFO] rcv: ' $buf '\n";
    } while (true);
    socket_close($msgsock);
} while (true);
socket_close($msgsock);
?>