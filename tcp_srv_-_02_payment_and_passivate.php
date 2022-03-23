<?php
/*
    tms-light - BxBot module
    automated test
    002 - terminal init

*/

include 'functions.php';
include 'config.php';

$testId     = '002';
$testName   = 'payment and passivate';
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
    if (($msgsock = socket_accept($msgsock)) === false) {
        echo "[ERROR] socket_accept() failed: reason: " . socket_strerror(socket_last_error($msgsock)) . "\n";
        break;
    }
    /* get client ip */
    socket_getpeername($msgsock, $clientIP);

    $connmsg = "[INFO] Got connect from ". $clientIP . " :-)" . " \n";
    //socket_write($msgsock, $connmsg, strlen($msg));
    echo($connmsg);

    $bprotcmd = hex2bin('024231303120202020202020203232303331313232313934323230303030303034413541351c54383003');
    socket_write($msgsock, $bprotcmd, strlen($bprotcmd));
    

    if (socket_read($msgsock, 2048, PHP_BINARY_READ) === false) {
        echo "[INFO] rcv: ' $buf '\n";
    }

    
    //
    // PAYMENT TERMINAL INITIALIZATION
    //

    // initialize terminal by appinfo command and enable keep-alive

    init_payment(12300, $msgsock);
    do {
        if (false === ($buf = socket_read($msgsock, 2048, PHP_BINARY_READ))) {
            echo "[ERROR] socket_read() failed: reason: " . socket_strerror(socket_last_error($msgsock)) . "\n";
            break 1;
        }
        handle_keepalive($buf, $msgsock);
 
        $talkback = $buf;
        socket_write($msgsock, $talkback, strlen($talkback));
        echo "[INFO] rcv: ' $buf '\n";
        sleep(1);

        if (strpos($buf, 'R-01') !== false) {
            $testResult = 0;
            echo "[INFO] test ok\n";
        } else {
            echo "[INFO] test failed\n";
            $testResult = 1;
            break;
        }
   
        


         
            
        }
        while(true);
        socket_close($msgsock);        
    }
    while(true);
    socket_close($msgsock);    
?>