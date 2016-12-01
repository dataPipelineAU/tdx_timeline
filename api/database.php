<?php
// TODO: Being strict during development. Remove the next two lines for production.
error_reporting(E_ALL);
ini_set('display_errors', True);

$production = false;

if ($production) {
//  gw20120601 - dev server
    $s_server = "dma.tdx.com.au";  //"192.168.254.193";
    $s_username = "root";
    $s_password = "tdxadmin10";
    $s_database = "tdx_timeline";
//*/
}else {
// gw20120601 - dev local pc
    $s_server = "localhost";
    $s_username = "ud";
    $s_password = "b77svL3mKC7Ymm4p";
    //$s_database = "tdxitwprolineow";
    //$s_database = "itwresow";
    $s_database = "tdx_timeline";
//*/
}

if (isset($_SESSION['DB_USERNAME'])){
    $s_username = $_SESSION['DB_USERNAME'];
}
if (isset($_SESSION['DB_PASSWORD'])){
    $s_password = $_SESSION['DB_PASSWORD'];
}
if (isset($_SESSION['MYSQL_LOCATION'])){
    $s_server = $_SESSION['MYSQL_LOCATION'];
}
if (isset($_SESSION['DATABASE_NAME'])){
    $s_database = $_SESSION['DATABASE_NAME'];
}


date_default_timezone_set('Australia/Melbourne');


function get_connection() {
    // Connect to database and return the connection
    global $s_server, $s_username, $s_password, $s_database;
    $mysqli = new mysqli($s_server, $s_username, $s_password, $s_database);
    if(mysqli_connect_errno()) {
        die("Connection Failed: " . mysqli_connect_errno());
    }
    return $mysqli;
}

