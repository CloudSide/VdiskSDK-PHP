<?php
require_once(dirname(__FILE__) . '/simpletest/autorun.php');
require_once(dirname(__FILE__) . '/config.php');
session_start();

class MetadataTester extends UnitTestCase {

	function testMetadataList() {
		
		//实例化 \Vdisk\OAuth2
		$oauth2 = new \Vdisk\OAuth2(VDISK_CLIENT_ID, VDISK_CLIENT_SECRET);
		$token = $oauth2->getTokenFromObject($_SESSION['token']);
		$client = new \Vdisk\Client($oauth2, 'basic');
		//$client->setDebug(true);
				
			$path = '/';
			// Attempt to retrieve the account information
			$response = $client->metaData($path);
			$metaData = $response['body'];

			/*
			[size] => 0 bytes
			[rev] => 1bd26196
			[thumb_exists] => 
			[bytes] => 0
			[modified] => Fri, 15 Mar 2013 03:06:35 +0000
			[path] => /
			[is_dir] => 1
			[root] => basic
			[icon] => folder
			[revision] => 0
			[is_deleted] =>
			[hash] => c6bb111aa75c2cc619fca7e77b1a1f5a
			*/
						
			$this->assertIsA($metaData, 'stdClass');
			$this->assertTrue($metaData->is_dir);
			$this->assertTrue(isset($metaData->rev));
			$this->assertTrue(isset($metaData->thumb_exists));
			$this->assertEqual($metaData->size, '0 bytes');
			$this->assertEqual($metaData->root, 'basic');
			$this->assertEqual($metaData->icon, 'folder');
			$this->assertTrue(isset($metaData->is_deleted));
			$this->assertPattern("/^[0-9a-f]{32}$/", $metaData->hash);
			$this->assertIsA($metaData->contents, 'array');
			
		
	}
	
	
	function testMetadataFile() {
			
		//未实现
	}
}