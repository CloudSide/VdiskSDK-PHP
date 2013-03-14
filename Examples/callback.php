<?php

/**
 * PHP SDK for Sina Vdisk (using OAuth2)
 * @author Bruce Chen <662005@qq.com>
 */

header('Content-Type: text/html; charset=UTF-8');

include_once('config.php');
session_start();

$oauth2 = new \Vdisk\OAuth2(VDISK_CLIENT_ID, VDISK_CLIENT_SECRET);
$oauth2->debug = false;

if (isset($_REQUEST['code'])) {
	
	$keys = array();
	$keys['code'] = $_REQUEST['code'];
	$keys['redirect_uri'] = VDISK_CALLBACK_URL;
	
	try {
		
		$token = $oauth2->getAccessToken('code', $keys);
		$_SESSION['token'] = $token;
		header('Location: index.php');
		
	} catch (Exception $e) {
		
		echo "<pre>";
		print_r($e->getMessage());
		echo "</pre>";
		echo "<a href='index.php'>返回</a>";
	}
}

/*

if ($token) {
	
	$_SESSION['token'] = $token;
	
	$client = new \Vdisk\Client($oauth2, 'basic');
	
	try {
		
		//$d = $client->accountInfo();
		$d = $client->metaData('/', null, 10000, false, false, false);
		print_r($d);
		
	} catch (\Vdisk\Exception $e) {
		
		echo "<pre>";
		echo '(' . get_class($e) . ') #' . $e->getCode() . ': [' . $e->getMessage() . ']';
		echo "</pre>";
		echo "<a href='index.php'>返回</a>";
	}
}

*/
