<?php

class page_utility_accwithoutimage extends Page {
	public $title ='Accounts Without Images';
	function init(){
		parent::init();

		$acc= $this->Add('Model_Account');
		$acc->addCondition('sig_image_id',null);
		$acc->addCondition('DefaultAC',0);
		$acc->addCondition('SchemeType','Default');
		$acc->add('Controller_Acl');

		$grid=$this->add('Grid');
		$grid->setModel($acc,array('AccountNumber','member_id'));
		$grid->addpaginator(100);
	}
}