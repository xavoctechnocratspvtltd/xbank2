<?php

class page_accounts extends Page {
	
	public $title = 'Accounts Manager';

	function init(){
		parent::init();
		throw $this->exception(' Exception text', 'ValidityCheck')->setField('FieldName');
		

		$tabs = $this->add('Tabs');
		foreach (explode(',',ACCOUNT_TYPES) as $accounts) {
			$accounts_display =$accounts;
			if($accounts == 'Default') $accounts_display = 'Others';

			$acc_tab = $tabs->addTabURL('accounts_'.$accounts,$accounts_display);
	
		}
	}
}