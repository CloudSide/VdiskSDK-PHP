<?php
include_once('Vdisk.php');

define('VDISK_CLIENT_ID', '1586711366');
define('VDISK_CLIENT_SECRET', 'c7c3711444291fffed9d4dbf469ee60f');
define('VDISK_CALLBACK_URL', 'http://vauth.appsina.com/Examples/callback.php');


$oauth2 = new \Vdisk\OAuth2(VDISK_CLIENT_ID, VDISK_CLIENT_SECRET, '5a6d586661O5V8N1JnG6O2Mz5Mv58a5a', '14e17e6661O5V8N1JnG6O2Mz5Mv7e660');
$client = new \Vdisk\Client($oauth2, 'basic');

$client->setDebug(true); //开启调试模式

/*
try {

    // Attempt to retrieve the account information
    //$response = $client->getFile('/IMG_0003.JPG', './IMG_0003.JPG');
	$response = $client->putFile('./IMG_0003.JPG', '/1212121.jpg');
   
    // Dump the output
    echo "<pre>";
    print_r($response); //打印用户信息
    echo "</pre>";

} catch (\Vdisk\Exception $e) { //捕获异常

    echo "<pre>";
    echo get_class($e) . ' ' . '#' . $e->getCode() . ': ' . $e->getMessage();
    echo "</pre>";
}
*/

try {

	$response = $client->putStream(fopen('./IMG_0003.JPG', 'r'), '/qwqwqwqwqwqw.jpg');
   
    // Dump the output
    echo "<pre>";
    print_r($response); //打印用户信息
    echo "</pre>";

} catch (\Vdisk\Exception $e) { //捕获异常

    echo "<pre>";
    echo get_class($e) . ' ' . '#' . $e->getCode() . ': ' . $e->getMessage();
    echo "</pre>";
}