<?php
class page_utility_shareprint extends Page{
	function init(){
		parent::init();
		$this->add('View_SharePrint')->setModel('Model_Account_SM');
	}
}