<?php
class page_utility_fdprint extends Page{
	function init(){
		parent::init();
		$this->add('View_FDPrint')->setModel('Model_Account_FixedAndMis');
	}
}