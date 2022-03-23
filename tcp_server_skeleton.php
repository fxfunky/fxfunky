<?php
/*
    simple simulator
    acting as tcp server

commands:

h, help
quit
shutdown, kill

B1 request format:

Head
B101________ TIMESTAMP FLAGS CRC 1c T00 1c B AMOUNT 1c 

Data



*/
include 'functions.php';
include 'config.php';


echo("\n" . '----------------------------------------' . "\n" . ' M+ Bp Test Tool' . "\n" . '----------------------------------------' . "\n");



// banner
echo("[INFO] Hello!" . "\n[INFO] Server listening on port: " . $port . "\n");
sleep(1);
echo("[INFO] Hello!" . "\n[INFO] Test 01 - Terminal init" . "\n");

// B1 request message preparation



// calculate reqest data lengh
$dataLen = str_pad(dechex(strlen($fieldSep . $operCmd . $amount . $fieldSep)), 4, "0", STR_PAD_LEFT);


//debug
error_reporting(E_ALL);
// or disable showing errors
//error_reporting(0);

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
    sleep(0.1);
    
    //
    // PAYMENT TERMINAL INITIALIZATION
    //

    // initialize terminal by appinfo command and enable keep-alive
    $bprotcmd = hex2bin('024231303120202020202020203232303331313232313934323230303030303034413541351c54383003');
    socket_write($msgsock, $bprotcmd, strlen($bprotcmd));
    sleep(1);

    // send passivate command to set terminal into known state
    $bprotcmd = hex2bin('024231303120202020202020203232303331323039353934313230303030303034413541351c54383103');
    socket_write($msgsock, $bprotcmd, strlen($bprotcmd));
    sleep(1);


    do {
        if (false === ($buf = socket_read($msgsock, 2048, PHP_BINARY_READ))) {
            echo "[ERROR] socket_read() failed: reason: " . socket_strerror(socket_last_error($msgsock)) . "\n";
            break 2;
        }
        /*if (!$buf = trim($buf)) {
            continue;
        }*/

        // remote server controll
        if ($buf == 'quit') {
            echo "[INFO] Client " . $clientIP ." left the chat...\n";
            break;
        }
        // remote server controll
        if (($buf == 'shutdown')  || ($buf == 'kill')) {
            echo "[INFO] $buf command received from $clientIP stopping server... bye.\n";
            sleep(1);
            socket_close($msgsock);
            break 2;
        }
        
        if ($buf == chr(5)) {
            echo "[INFO] keepalive ENQ  \n";
            sleep(1);
            socket_write($msgsock, chr(0x06), strlen(2));
            echo "[INFO] keepalive ACK  \n";
            sleep(1);
            echo "[INFO] Insert amount (or special): \n";
            $handle = fopen ("php://stdin","r");
            $input = fgets($handle);

            // catch number by default
            if (trim($input) != 0) {
                init_payment(trim($input));
            }

            // payment cancelation - send passivate command
            if (trim($input) == 'p') {
                echo "[INFO] Payment cancelation requested \n";
                cancel_payment();
            }

            // perform connection test to authorization host
            if (trim($input) == 'h') {
                echo "[INFO] Terminal connection test requested \n";
                init_handshake();
            }

            // refund operation
            if (trim($input) == 'r') {
                echo "[INFO] Amount of refund: ";
                $handle = fopen ("php://stdin","r");
                $input = fgets($handle);
                init_refund(trim($input));
            }
            continue;          
        }

        $talkback = $buf;
        socket_write($msgsock, $talkback, strlen($talkback));
        echo "[INFO] rcv: ' $buf '\n";
    } while (true);
    socket_close($msgsock);
} while (true);
socket_close($sock);
?>