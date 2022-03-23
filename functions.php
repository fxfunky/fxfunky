<?php

// global variables

$reqPrefix = chr(2) . 'B101'; 
$emptyTid  = '        ';
$timestamp = date('ymdHis');
$optsMask  = '2000';
$crcCode   = 'A5A5';
$msgEnd    = chr(3);
$fieldSep  = chr(0x1c);
$amount    = "B" . "12305";
$operCmd   = 'T00';
$buf       = '';


// functions

/**
 * Does something interesting
 *
 * @param socketName   $where  Where something interesting takes place
 * @param integer $repeat How many times something interesting should happen
 * 
 * @throws Some_Exception_Class If something interesting cannot happen
 * @author fxlab <dump@fxlx.me>
 * @return Status
 */ 
function init_payment($amount, $socketName) {
    $operCmd = "T00";
    $dataLen = str_pad(dechex(strlen(chr(0x1c) . $operCmd . "B" . $amount . chr(0x1c))), 4, "0", STR_PAD_LEFT);
    //error_log("[DEBUG] Calculated data lengh is: " . $dataLen);
    $request = chr(0x02) . "B101" . $GLOBALS['emptyTid'] . $GLOBALS['timestamp'] . $GLOBALS['optsMask'] . $dataLen . $GLOBALS['crcCode'] . chr(0x1c) . $operCmd . chr(0x1c) . "B" . $amount . chr(0x03);
    //error_log("[DEBUG] Generated B1 request  is: " . $request);
    echo "[INFO] Payment amount = $amount\n";
    echo "[INTERACT] !!! PLEASE USE CARD !!! or robot hook\n";
    socket_write($socketName, $request, strlen($request));
    sleep(1);
}
/**
 * Does something interesting
 *
 * @param socketName   $where  Where something interesting takes place
 * @param integer $repeat How many times something interesting should happen
 * 
 * @throws Some_Exception_Class If something interesting cannot happen
 * @author fxlab <dump@fxlx.me>
 * @return Status
 */ 
function cancel_payment($socketName) {
    $operCmd = "T81";
    $dataLen = str_pad(dechex(strlen(chr(0x1c) . $operCmd)), 4, "0", STR_PAD_LEFT);
    //error_log("[DEBUG] Calculated data lengh is: " . $dataLen);
    echo "[INFO] Cancel payment\n";
    $request = chr(0x02) . "B101" . $GLOBALS['emptyTid'] . $GLOBALS['timestamp'] . $GLOBALS['optsMask'] . $dataLen . $GLOBALS['crcCode'] . chr(0x1c) . $operCmd . chr(0x03);
    //error_log("[DEBUG] Generated B1 request  is: " . $request);
    socket_write($socketName, $request, strlen($request));
    sleep(5);
}

function init_handshake($socketName) {
    $operCmd = "T95";
    $dataLen = str_pad(dechex(strlen(chr(0x1c) . $operCmd)), 4, "0", STR_PAD_LEFT);
    //error_log("[DEBUG] Calculated data lengh is: " . $dataLen);
    $request = chr(0x02) . "B101" . $GLOBALS['emptyTid'] . $GLOBALS['timestamp'] . $GLOBALS['optsMask'] . $dataLen . $GLOBALS['crcCode'] . chr(0x1c) . $operCmd . chr(0x03);
    //error_log("[DEBUG] Generated B1 request  is: " . $request);
    socket_write($socketName, $request, strlen($request));
    sleep(1);
}

function call_to_tms($socketName) {
    $operCmd = "T90";
    $dataLen = str_pad(dechex(strlen(chr(0x1c) . $operCmd)), 4, "0", STR_PAD_LEFT);
    //error_log("[DEBUG] Calculated data lengh is: " . $dataLen);
    $request = chr(0x02) . "B101" . $GLOBALS['emptyTid'] . $GLOBALS['timestamp'] . $GLOBALS['optsMask'] . $dataLen . $GLOBALS['crcCode'] . chr(0x1c) . $operCmd . chr(0x03);
    //error_log("[DEBUG] Generated B1 request  is: " . $request);
    socket_write($socketName, $request, strlen($request));
    sleep(1);
}


function terminal_appinfo($socketName) {
    $operCmd = "T80";
    $dataLen = str_pad(dechex(strlen(chr(0x1c) . $operCmd)), 4, "0", STR_PAD_LEFT);
    //error_log("[DEBUG] Calculated data lengh is: " . $dataLen);
    $request = chr(0x02) . "B101" . $GLOBALS['emptyTid'] . $GLOBALS['timestamp'] . $GLOBALS['optsMask'] . $dataLen . $GLOBALS['crcCode'] . chr(0x1c) . $operCmd . chr(0x03);
    //error_log("[DEBUG] Generated B1 request  is: " . $request);
    socket_write($socketName, $request, strlen($request));
    sleep(1);
}

function init_refund($amount, $socketName) {
    $operCmd = "T04";
    $dataLen = str_pad(dechex(strlen(chr(0x1c) . $operCmd . "B" . $amount . chr(0x1c))), 4, "0", STR_PAD_LEFT);
    error_log("[DEBUG] Calculated data lengh is: " . $dataLen);
    echo "[INFO] Refund for = $amount \n";
    $request = chr(0x02) . "B101" . $GLOBALS['emptyTid'] . $GLOBALS['timestamp'] . $GLOBALS['optsMask'] . $dataLen . $GLOBALS['crcCode'] . chr(0x1c) . $operCmd . chr(0x1c) . "B" . $amount . chr(0x03);
    error_log("[DEBUG] Generated B1 request  is: " . $request);
    socket_write($socketName, $request, strlen($request));
    sleep(1);
}

function handle_keepalive($socketBuffer, $socketName) {
    if ($socketBuffer == chr(5)) {
        echo "[INFO] keepalive ENQ  \n";
        sleep(1);
        socket_write($socketName, chr(0x06), strlen(2));
        echo "[INFO] keepalive ACK  \n";
        sleep(1);
    } else {
        return 0;
        }
}



//
// service functions
//

// enable or disable debug by 0 or 1 - used in config.php
function set_debug($trigger) {
    if ($trigger == 0) {
        error_reporting(0);
        ini_set('display_errors', 1);
    }
    elseif ($trigger == 1) {
        echo "[INFO] Debug mode enabled\n";
        error_reporting(E_ALL);
    } else {
    echo "[ERROR] debug not set properly!!! check config.php \n";
    exit; }
}

/* text announce on test's start-up */
function get_banner() {
    echo("\n" . '----------------------------------------' . "\n" . ' M+ Bp Test Tool' . "\n" . '----------------------------------------' . "\n");
    echo("[INFO] Hello!" . "\n[INFO] Server listening on port: " .  $GLOBALS['port'] . "\n");
    echo("[INFO] Test " . $GLOBALS['testId'] . " - " . $GLOBALS['testName'] . "\n");
}

// write down test result
// test result should be 0 for success or 1 if test fails
function result_dbinsert($dbHost, $dbUser) {
    // gather some data - clientIP, testId,testName, testResult



    // 1.initialize connection to db
    $conn = new mysqli($dbHost, $dbUser, $GLOBALS['dbPwd']);
    // 2. check mysql connection
    if ($conn->connect_error) {
        die("[ERROR] Database connection failed: " . $conn->connect_error) . "\n";
    }
        echo "[INFO] Database connected successfully\n";

        echo("[DEBUG] data gathered for db:" . $GLOBALS['testId'] . $GLOBALS['testName'] . $GLOBALS['clientIP'] . $GLOBALS['testResult'] . "\n");

    // DB INSERT

    return 0;
}


?>