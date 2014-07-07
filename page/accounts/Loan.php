<?php

class page_accounts_Loan extends Page {

	function page_index(){
		$tabs=$this->add('Tabs');
		$accounts_tab = $tabs->addTabURL($this->api->url('./accounts'),'Accounts');
		$pending_accounts_tab = $tabs->addTabURL($this->api->url('./pendingAccounts'),'Pending');
	}

	function page_pendingAccounts(){
		$crud=$this->add('xCRUD');
		$account_loan_model = $this->add('Model_Account_Loan',array('table'=>'accounts_pending'));

		$account_loan_model->add('Controller_Acl');
		$account_loan_model->setOrder('id','desc');
		
		$crud->addHook('myupdate',function($crud,$form){
			if($crud->isEditing('edit')) return;
			
			if($form['LoanAgSecurity'] AND !$form['LoanAgainstAccount_id'])
				$form->displayError('LoanAgainstAccount','Please Specify Loan Against Account Number');

			$loan_account_model = $crud->add('Model_Account_Loan');
			$loan_account_model->createNewPendingAccount($form['member_id'],$form['scheme_id'],$crud->api->current_branch, $form['AccountNumber'],$form->getAllFields(),$form);
			return true;
		});


		/**
		 * Add Documents Fields ...
		 */
		if($crud->isEditing("add")){
		    $o=$crud->form->add('Order');
			$documents=$this->add('Model_Document');
			$documents->addCondition('LoanAccount',true);
			foreach ($documents as $d) {
			    $f1=$crud->form->addField('checkbox',$this->api->normalizeName($documents['name']));
			   	$o->move($f1,'last');
			    $f2=$crud->form->addField('line',$this->api->normalizeName($documents['name'].' value'));
			   	$o->move($f2,'last');
			   	$f1->js(true)->univ()->bindConditionalShow(array(
					''=>array(''),
					'*'=>array($this->api->normalizeName($documents['name'].' value'))
					),'div .atk-form-row');
			}
			$loan_from_account_field = $crud->form->addField('autocomplete/Basic','loan_from_account')->validateNotNull();
			$loan_from_account_field->setModel('Account');
		}



		if($crud->isEditing('edit')){
			// $account_loan_model->hook('editing');
		}
		
		$crud->setModel($account_loan_model,array('account_type','AccountNumber','member_id','scheme_id','Amount','agent_id','ActiveStatus','gaurantor','gaurantorAddress','gaurantorPhNo','ModeOfOperation','loan_from_account_id','LoanInsurranceDate','LoanAgainstAccount_id','dealer_id'),array('AccountNumber','member','scheme','Amount','agent','ActiveStatus','gaurantor','gaurantorAddress','gaurantorPhNo','ModeOfOperation','loan_from_account','LoanInsurranceDate','LoanAgainstAccount','dealer'));

		if($crud->isEditing()){
			$crud->form->getElement('account_type')->setEmptyText('Please Select');
		}

		if($crud->isEditing('add')){

			$f1=$crud->form->getElement('account_type');
			$f1->js(true)->univ()->bindConditionalShow(array(
					''=>array(''),
					'Loan Againest Deposit'=>array('LoanAgainstAccount','LoanAgainstAccount_id')
					),'div .atk-form-row');

			$crud->form->add('Order')
						// ->move('LoanAgSecurity','after','LoanInsurranceDate')
						->move($loan_from_account_field->other_field,'after','LoanInsurranceDate')
						->now();
			$o->now();
		}


		if($crud->grid){
			$crud->grid->addPaginator(10);
			$crud->grid->addColumn('expander','edit_pendingDocument');
			$crud->grid->addColumn('expander','action');
		}
	}

	function page_accounts(){

		
		$crud=$this->add('xCRUD',array('allow_add'=>false));
		$account_loan_model = $this->add('Model_Account_Loan');

		$account_loan_model->add('Controller_Acl');
		$account_loan_model->setOrder('id','desc');
		
		$crud->addHook('myupdate',function($crud,$form){
			if($crud->isEditing('edit')) return;
			
			if($form['LoanAgSecurity'] AND !$form['LoanAgainstAccount_id'])
				$form->displayError('LoanAgainstAccount','Please Specify Loan Against Account Number');

			$loan_account_model = $crud->add('Model_Account_Loan');
			$loan_account_model->createNewPendingAccount($form['member_id'],$form['scheme_id'],$crud->api->current_branch, $form['AccountNumber'],$form->getAllFields(),$form);
			return true;
		});


		/**
		 * Add Documents Fields ...
		 */
		if($crud->isEditing("add")){
		    $o=$crud->form->add('Order');
			$documents=$this->add('Model_Document');
			$documents->addCondition('LoanAccount',true);
			foreach ($documents as $d) {
			    $f1=$crud->form->addField('checkbox',$this->api->normalizeName($documents['name']));
			   	$o->move($f1,'last');
			    $f2=$crud->form->addField('line',$this->api->normalizeName($documents['name'].' value'));
			   	$o->move($f2,'last');
			   	$f1->js(true)->univ()->bindConditionalShow(array(
					''=>array(''),
					'*'=>array($this->api->normalizeName($documents['name'].' value'))
					),'div .atk-form-row');
			}
			$loan_from_account_field = $crud->form->addField('autocomplete/Basic','loan_from_account')->validateNotNull();
			$loan_from_account_field->setModel('Account');
		}



		if($crud->isEditing('edit')){
			$account_loan_model->hook('editing');
		}
		
		$crud->setModel($account_loan_model,array('account_type','AccountNumber','member_id','scheme_id','Amount','agent_id','ActiveStatus','gaurantor','gaurantorAddress','gaurantorPhNo','ModeOfOperation','loan_from_account_id','LoanInsurranceDate','LoanAgainstAccount_id','dealer_id'),array('AccountNumber','member','scheme','Amount','agent','ActiveStatus','gaurantor','gaurantorAddress','gaurantorPhNo','ModeOfOperation','loan_from_account','LoanInsurranceDate','LoanAgainstAccount','dealer'));

		if($crud->isEditing()){
			$crud->form->getElement('account_type')->setEmptyText('Please Select');
		}

		if($crud->isEditing('add')){

			$f1=$crud->form->getElement('account_type');
			$f1->js(true)->univ()->bindConditionalShow(array(
					''=>array(''),
					'Loan Againest Deposit'=>array('LoanAgainstAccount','LoanAgainstAccount_id')
					),'div .atk-form-row');

			$crud->form->add('Order')
						// ->move('LoanAgSecurity','after','LoanInsurranceDate')
						->move($loan_from_account_field->other_field,'after','LoanInsurranceDate')
						->now();
			$o->now();
		}


		if($crud->grid){
			$crud->grid->addPaginator(10);
			$crud->grid->addColumn('expander','edit_document');
		}

	}

	function page_pendingAccounts_edit_pendingDocument(){
		$this->api->stickyGET('accounts_pending_id');
		$pending_account = $this->add('Model_PendingAccount');
		$pending_account->load($_GET['accounts_pending_id']);

		$extra_info = json_decode($pending_account['extra_info'],true);
		$doc_info =$extra_info['documents_feeded'];

		$form = $this->add('Form');
		$o=$form->add('Order');

		$documents=$this->add('Model_Document');
		$documents->addCondition('LoanAccount',true);
		foreach ($documents as $d) {
		    $f1=$form->addField('checkbox',$this->api->normalizeName($documents['name']));
		   	$o->move($f1,'last');
		    $f2=$form->addField('line',$this->api->normalizeName($documents['name'].' value'));
		   	$o->move($f2,'last');
		   	if($doc_info[$this->api->normalizeName($documents['name'])]){
		   		$f1->set(1);
		   		$f2->set($doc_info[$this->api->normalizeName($documents['name'])]);
		   	}
		   	$f1->js(true)->univ()->bindConditionalShow(array(
				''=>array(''),
				'*'=>array($this->api->normalizeName($documents['name'].' value'))
				),'div .atk-form-row');
		}

		$form->addSubmit('Update');

		if($form->isSubmitted()){
			$documents=$this->add('Model_Document');
			$documents_feeded = array();
			foreach ($documents as $d) {
			 	if($form[$this->api->normalizeName($documents['name'])]){
					$doc_info[$this->api->normalizeName($documents['name'])]=$form[$this->api->normalizeName($documents['name'].' value')];
			 	}else{
			 		unset($doc_info[$this->api->normalizeName($documents['name'])]);
			 	}
			}
			$extra_info['documents_feeded'] = $doc_info;
			$pending_account['extra_info'] = json_encode($extra_info);
			$pending_account->save();
			$form->js()->univ()->closeExpander()->execute();

		}
	}

	function page_pendingAccounts_action(){
		$this->api->stickyGET('accounts_pending_id');
		$pending_account = $this->add('Model_PendingAccount');
		$pending_account->load($_GET['accounts_pending_id']);
		

		$approve_btn = $this->add('Button')->set('APPROVE');
		$reject_btn = $this->add('Button')->set('REJECT');

		if($approve_btn->isClicked('Are you sure to approve and create account')){
			$pending_account->approve();
		}

		if($reject_btn->isClicked('Are you sure')){
			$pending_account->reject();
		}

	}

	function page_accounts_edit_document(){
		$this->api->stickyGET('accounts_id');

		$documents=$this->add('Model_DocumentSubmitted');
		$documents->addCondition('accounts_id',$_GET['accounts_id']);

		$crud=$this->add('CRUD',array('allow_add'=>true));
		$crud->setModel($documents);
	}
}