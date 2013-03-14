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
		//$client->setDebug(true);
		
		try {
			
			if (isset($_GET['path'])) {
				
				$path = $_GET['path'];
			
			} else {
				
				$path = '/';	
			}
			
			// Attempt to retrieve the account information
			$response = $client->metaData($path);
			$metaData = $response['body'];

		} catch (\Vdisk\Exception $e) {
			
			echo "<pre>";
			echo get_class($e) . ' ' . '#' . $e->getCode() . ': ' . $e->getMessage();
			echo "</pre>";
		}

} else {
	
	echo "您还没有登录, <a href='index.php'>去登录</a>";
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

<?php if ($metaData) { ?>

<div><h2>Index Of: <?=$metaData->path?></h2></div>

<?php

if (isset($metaData->contents)) {

?>

<div>
	<ul>
		
		<?php

			foreach ($metaData->contents as $value) {
		?>
		
		<li>
			<img src="images/<?=$value->icon;?>.png" /><a href="metaData.php?path=<?=urlencode($value->path);?>"><?=end(explode('/', $value->path));?></a> 
			&nbsp;&nbsp;&nbsp;
			<?php 
				
				if (!$value->is_dir) {
					
					echo $value->size;
				}
			?>
			&nbsp;&nbsp;&nbsp;
			<?=date('Y-m-d H:i:s', strtotime($value->modified));?>
		</li>
		
		<?php 
			}
		?>
		
	</ul>
</div>

<?php } } ?>

</body>
</html>

