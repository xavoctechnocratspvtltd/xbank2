<?php

class page_accounts_Loan extends Page {

	function page_index(){

		$crud=$this->add('xCRUD');
		$account_loan_model = $this->add('Model_Account_Loan');
		$account_loan_model->add('Controller_Acl');
		
		$crud->addHook('myupdate',function($crud,$form){
			if($crud->isEditing('edit')) return;

			if($form['LoanAgSecurity'] AND !$form['LoanAgainstAccount_id'])
				$form->displayError('LoanAgainstAccount','Please Specify Loan Against Account Number');

			$loan_account_model = $crud->add('Model_Account_Loan');
			$loan_account_model->createNewAccount($form['member_id'],$form['scheme_id'],$crud->api->current_branch, $form['AccountNumber'],$form->getAllFields(),$form);
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
			$f1=$crud->form->addField('checkbox','LoanAgSecurity');
			$f1->js(true)->univ()->bindConditionalShow(array(
					''=>array(''),
					'*'=>array('LoanAgainstAccount','LoanAgainstAccount_id')
					),'div .atk-form-row');
			$loan_from_account_field = $crud->form->addField('autocomplete/Basic','loan_from_account')->validateNotNull();
			$loan_from_account_field->setModel('Account');
		}



		if($crud->isEditing('edit')){
			$account_loan_model->hook('editing');
		}
		
		$crud->setModel($account_loan_model,array('AccountNumber','member_id','scheme_id','Amount','agent_id','ActiveStatus','gaurantor','gaurantorAddress','gaurantorPhNo','ModeOfOperation','loan_from_account_id','LoanInsurranceDate','LoanAgainstAccount_id','dealer_id'),array('AccountNumber','member','scheme','Amount','agent','ActiveStatus','gaurantor','gaurantorAddress','gaurantorPhNo','ModeOfOperation','loan_from_account','LoanInsurranceDate','LoanAgainstAccount','dealer'));
		
		if($crud->isEditing('add')){
			$crud->form->add('Order')
						->move('LoanAgSecurity','after','LoanInsurranceDate')
						->move($loan_from_account_field->other_field,'after','LoanInsurranceDate')
						->now();
			$o->now();
		}


		if($crud->grid){
			$crud->grid->addPaginator(10);
			$crud->grid->addColumn('expander','edit_document');
		}

	}

	function page_edit_document(){
		$this->api->stickyGET('accounts_id');

		$documents=$this->add('Model_DocumentSubmitted');
		$documents->addCondition('accounts_id',$_GET['accounts_id']);

		$crud=$this->add('CRUD',array('allow_add'=>true));
		$crud->setModel($documents);
	}
}