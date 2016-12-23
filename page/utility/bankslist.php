<?php
class page_utility_bankslist extends Page{
	public $title ="Banks Management";

	function init(){
		parent::init();

		$crud = $this->add('CRUD');
		$crud->setModel('Bank');

		$crud->addRef('BankBranches');
		
	}
}