<?php

class page_accounts extends Page {
	
	public $title = 'Accounts Manager';

	function init(){
		parent::init();

		

		$tabs = $this->add('Tabs');
		foreach (explode(',',ACCOUNT_TYPES) as $accounts) {
			$acc_tab = $tabs->addTabURL('accounts_'.$accounts,$accounts);
	
		}
	}
}