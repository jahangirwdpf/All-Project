<?php

global $config;
date_default_timezone_set('Europe/London');

define("TIME",time());
if($_SERVER['SERVER_NAME']=='localhost' || $_SERVER['SERVER_NAME']=='::1'){
    define('DB_SERVER', 'localhost');
    define('DB_USERNAME', 'root');
    define('DB_PASSWORD', '');
    define('DB_DATABASE', 'user_db');
    $config['base_url'] = 'http://localhost/ooapi';
    $config['api_url'] = 'http://localhost/ooapi';
}else{
    // echo 'something wrong';exit;
    // live server credentials 
}


global $connection;
$connection = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_DATABASE);
if (mysqli_connect_errno()){
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
}
date_default_timezone_set('Asia/Dhaka');

$config['url'] =  $config['file_url'].'/template/'.$config['temp_name'];
$config['admin_url'] = $config['base_url'].'/admin';
$config['currency'] = '£ ';
?>
