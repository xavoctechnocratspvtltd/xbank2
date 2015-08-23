<?php

class page_utility_accwithoutimage extends Page {
	public $title ='Accounts Without Images';
	function init(){
		parent::init();

		$acc= $this->Add('Model_Account');
		$acc->addCondition('sig_image_id',null);
		$acc->addCondition('DefaultAC',0);
		$acc->addCondition('ActiveStatus',true);
		$acc->addCondition('SchemeType',explode(",", "DDS,Loan,CC,FixedAndMis,SavingAndCurrent,Recurring"));
		$acc->setOrder('AccountNumber');
		// $acc->addCondition('SchemeType','<>','Default');
		$acc->add('Controller_Acl');

		$grid=$this->add('Grid_AccountsBase');
		$grid->setModel($acc,array('AccountNumber','member_id'));
		$grid->addpaginator(500);
		$grid->addSno();
	}
}