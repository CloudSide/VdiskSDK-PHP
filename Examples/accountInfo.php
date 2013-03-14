<?php

include_once('config.php');
session_start();


//实例化 \Vdisk\OAuth2
$oauth2 = new \Vdisk\OAuth2(VDISK_CLIENT_ID, VDISK_CLIENT_SECRET);

//判断是否登录, 并且给$oauth2->accessToken负值
if (isset($_SESSION['token']) && 
	isset($_SESSION['token']->access_token) && 
	$token = $oauth2->getTokenFromObject($_SESSION['token'])) {
		
		$client = new \Vdisk\Client($oauth2, 'basic');
		$client->setDebug(true);
		
		try {
			
			// Attempt to retrieve the account information
			$response = $client->accountInfo();
			$accountInfo = $response['body'];
			// Dump the output
			echo "<pre>";
			print_r($accountInfo);
			echo "</pre>";

		} catch (\Vdisk\Exception $e) {
			
			echo "<pre>";
			echo get_class($e) . ' ' . '#' . $e->getCode() . ': ' . $e->getMessage();
			echo "</pre>";
		}

} else {
	
	echo "您还没有登录, <a href='index.php'>去登录</a>";
}
