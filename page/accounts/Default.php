<?php

class page_accounts_Default extends Page {
	function init(){
		parent::init();


		$crud=$this->add('xCRUD');
		$account_default_model = $this->add('Model_Account_Default');
		$account_default_model->add('Controller_Acl');

		if($crud->isEditing('edit')){
			$account_default_model->hook('editing');
		}

		$crud->setModel($account_default_model,array('AccountNumber','member_id','scheme_id','agent_id','ActiveStatus'));

		
		if($crud->grid)
			$crud->grid->addPaginator(10);
	}
}