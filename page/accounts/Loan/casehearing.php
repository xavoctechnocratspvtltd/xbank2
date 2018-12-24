<?php

class page_accounts_Loan_casehearing extends Page {
	public $title = "Legal Case Hearing Manage";

	function init(){
		parent::init();

		$this->add('Controller_Acl');
		
		$crud = $this->add('CRUD');
		$crud->setModel('LegalCase');
		$crud->addRef('LegalCaseHearing',['grid_fields'=>['legalcase','hearing_date','stage','owner','dealer']]);
	}
}