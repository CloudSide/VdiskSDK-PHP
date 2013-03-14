<?php

/**
 * PHP SDK for Sina Vdisk (using OAuth2)
 * @author Bruce Chen <662005@qq.com>
 */

/*

phpdoc run -f ./Vdisk/Vdisk.php -t ./Doc --sourcecode --title "新浪微盘SDK For PHP" --hidden --validate

*/

include_once('config.php');
session_start();

if (isset($_GET['logout']) && $_GET['logout'] == 1) { //退出登录, 清除SESSION
	
	session_destroy();
	header('Location: index.php');
}


$is_login = false;

//实例化 \Vdisk\OAuth2
$oauth2 = new \Vdisk\OAuth2(VDISK_CLIENT_ID, VDISK_CLIENT_SECRET);

//判断是否登录, 并且给$oauth2->accessToken负值
if (isset($_SESSION['token']) && 
	isset($_SESSION['token']->access_token) && 
	$token = $oauth2->getTokenFromObject($_SESSION['token'])) {
	
	$is_login = true;
}



?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
  <meta http-equiv="Content-Style-Type" content="text/css">
  <title>新浪微盘SDK实例</title>
  <meta name="Generator" content="Cocoa HTML Writer">
  <meta name="CocoaVersion" content="1187.34">
  <style type="text/css"></style>
</head>
<body>

<?php if ($is_login) { ?>

<div><a href="index.php?logout=1&<?=time();?>">退出登录</a></div>
<div>
	<ul>
		
		<li><a href="accountInfo.php">获得用户信息</a></li>
		<li><a href="metaData.php">获得文件/目录信息</a></li>
		
	</ul>
</div>

<?php 

	echo "<pre>";
	print_r($token);
	echo "</pre>";

} else { ?>

<a href="<?=$oauth2->getAuthorizeURL(VDISK_CALLBACK_URL, 'code', time(), 'mobile');?>">点击授权并登录微盘</a>

<?php } ?>

</body>
</html>
