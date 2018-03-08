<?php

class page_accounts extends Page {
	
	public $title = 'Accounts Manager';

	function init(){
		parent::init();
		

		$tabs = $this->add('Tabs');
		$tabs->addTabURL('accounts_SM','SM Accounts');
		foreach (explode(',','SavingAndCurrent,DDS,DDS2,Recurring,FixedAndMis,Loan,Default') as $accounts) {
			$accounts_display =$accounts;
			if($accounts == 'Default') $accounts_display = 'Others';

			$acc_tab = $tabs->addTabURL('accounts_'.$accounts,$accounts_display);
	
		}

	}
}