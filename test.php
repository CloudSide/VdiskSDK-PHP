<?php
header('Content-Type: text/html; charset=UTF-8');
require_once('Tests/simpletest/autorun.php');

class AllTests extends TestSuite {
	
    public function __construct() {
    
	    parent::__construct();
        $this->collect(dirname(__FILE__) . '/Tests', new SimplePatternCollector('/Tester.php/'));
	}
}