<?php

class page_accounts_locking extends Page {
	public $title='Lock & UnLock Accounts';
	function init(){
		parent::init();

		$form=$this->add('Form');
		$account_field=$form->addField('autocomplete/Basic','account_number');
		$account_field->setModel('Account');
		$form->addSubmit('GET Account');

		$grid=$this->add('Grid');
		$accounts=$this->add('Model_Account');
		if($_GET['account'])
			$accounts->addCondition('id',$_GET['account']);
		else
			$accounts->addCondition('id',-1);

		if($_GET['swap_locking_status']){
			$accounts->load($_GET['swap_locking_status']);
			$accounts->swapLockingStatus();
			$grid->js()->reload()->execute();
		}
		$grid->setModel($accounts,array('AccountNumber','branch','staff','member','LockingStatus'));
		$grid->addPaginator(10);
		$grid->addColumn('button','swap_locking_status');

		if($form->isSubmitted()){
			$grid->js()->reload(array('account'=>$form['account_number']))->execute();
		}
	}
}