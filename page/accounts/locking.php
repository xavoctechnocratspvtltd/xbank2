<?php

class page_accounts_locking extends Page {
	public $title='Lock & UnLock Accounts';
	function init(){
		parent::init();

		$this->add('Controller_Acl',['default_view'=>false]);

		$form=$this->add('Form');
		$account_field=$form->addField('autocomplete/Basic','account_number');
		$account_field->setModel('Account');
		$form->addSubmit('GET Account');

		$grid=$this->add('Grid');
		$accounts=$this->add('Model_Account');
		if($this->api->stickyGET('account'))
			$accounts->addCondition('id',$_GET['account']);
		else
			$accounts->addCondition('id',-1);

		if($_GET['swap_locking_status']){
			if(!$_GET['value']) throw new Exception("Specify reason when changing lock status", 1);
			$accounts->load($_GET['swap_locking_status']);
			$accounts->swapLockingStatus($_GET['value']);
			$grid->js()->reload()->execute();
		}

		if($_GET['swap_active_status']){
			$accounts->load($_GET['swap_active_status']);
			$accounts->swapActiveStatus();
			$grid->js()->reload()->execute();
		}


		if($_GET['swap_matured_status']){
			$accounts->load($_GET['swap_matured_status']);
			$accounts->swapMaturedStatus();
			$grid->js()->reload()->execute();
		}

		$grid->setModel($accounts,array('AccountNumber','branch','staff','member','LockingStatus','lock_status_changed_reason','ActiveStatus','MaturedStatus'));
		$grid->addPaginator(10);
		$grid->addColumn('Prompt','swap_locking_status');
		$grid->addColumn('button','swap_active_status');
		$grid->addColumn('button','swap_matured_status');

		if($form->isSubmitted()){
			$grid->js()->reload(array('account'=>$form['account_number']))->execute();
		}
	}
}