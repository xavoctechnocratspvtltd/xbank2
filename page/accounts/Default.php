<?php

class page_accounts_Default extends Page {
	function init(){
		parent::init();


		$crud=$this->add('CRUD');
		$account_default_model = $this->add('Model_Account_Default');
		$account_default_model->add('Controller_Acl');

		$crud->setModel($account_default_model,array('AccountNumber','member_id','scheme_id','agent_id','ActiveStatus'));

		
		if($crud->grid)
			$crud->grid->addPaginator(10);
	}
}