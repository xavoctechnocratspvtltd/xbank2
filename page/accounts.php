<?php

class page_accounts extends Page {
	
	public $title = 'Accounts Manager';

	function init(){
		parent::init();
		

		$tabs = $this->add('Tabs');
		$tabs->addTabURL('accounts_SM','SM Accounts');
		
		$req_arr= explode(",", 'SavingAndCurrent,DDS2,Recurring,FixedAndMis,Loan,Default');
		// $req_arr= explode(",", 'SavingAndCurrent,DDS,DDS2,Recurring,FixedAndMis,Loan,Default');
		$config_acctype_arr =explode(",", ACCOUNT_TYPES);

		$arr= array_intersect($req_arr, $config_acctype_arr);

		foreach ($arr as $accounts) {
			$accounts_display =$accounts;
			if($accounts == 'Default') $accounts_display = 'Others';

			$acc_tab = $tabs->addTabURL('accounts_'.$accounts,$accounts_display);
	
		}

	}
}