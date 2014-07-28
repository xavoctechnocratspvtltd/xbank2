<?php

class page_operations_edit extends Page {
	public $title='Admin Edit Operations';

	function init(){
		parent::init();

		$tabs = $this->add('Tabs');
		$tabs->addTabURL('accounts_Loan_accounts_edit','Loan');

	}
}