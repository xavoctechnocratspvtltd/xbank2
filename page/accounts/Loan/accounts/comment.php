<?php

class page_accounts_Loan_accounts_comment extends Page{
	function init(){
		parent::init();

		$this->api->stickyGET('accounts_id');

		$account=$this->add('Model_Account_Loan');
		$account->load($_GET['accounts_id']);

		$form=$this->add('Form');
		$form->addField('text','narration');
		$form->addSubmit('Save');
		$grid=$this->add('Grid');
		$comment=$this->add('Model_Comment');
		$comment->addCondition('account_id',$_GET['accounts_id']);
		$grid->setModel($comment,array('account','narration','created_at'));
		if($form->isSubmitted()){
			$comment=$this->add('Model_Comment');
			$comment->createNew($form['narration'],null,$account);
			$form->js(null,$grid->js()->reload())->univ()->closeExpander()->execute();
		}

	}
}