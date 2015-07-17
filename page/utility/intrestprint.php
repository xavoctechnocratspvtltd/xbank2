<?php
class page_utility_intrestprint extends Page{
	function init(){
		parent::init();
		$this->add('View_IntrestPrint')->setModel('Model_Account_FixedAndMis');
	}
}