<?php
class page_utility_bankslist extends Page{
	public $title ="Banks Management";

	function init(){
		parent::init();

		$this->add('Controller_Acl',['default_view'=>false]);
		$crud = $this->add('CRUD');
		$crud->setModel('Bank');
		$crud->add('Controller_Acl');

		$sub_view = $crud->addRef('BankBranches');
		$s = $this->api->normalizeName('BankBranches');

        if (isset($_GET['expander'])) {
			$sub_view->add('Controller_Acl',['default_view'=>false]);
        }
		
	}
}