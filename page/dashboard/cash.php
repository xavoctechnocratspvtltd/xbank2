<?php

class page_dashboard_cash extends Page {
	function init(){
		parent::init();

		$heading = $this->add('H2')->set(array('Cash Report','icon'=>'flag'));
		$grid=$this->add('Grid');
		$accounts=$this->add('Model_Account');
		$accounts->addCondition('scheme_name',BANK_ACCOUNTS_SCHEME);
		$accounts->add('Controller_Acl');
		$grid->setModel($accounts);

		 $this->add('H2')->set(array('Bank Report','icon'=>'flag'));
		$grid=$this->add('Grid');
		$account_cash=$this->add('Model_Account');
		$account_cash->addCondition('scheme_name',CASH_ACCOUNT);
		$account_cash->add('Controller_Acl');
		$grid->setModel($account_cash);
	}
}