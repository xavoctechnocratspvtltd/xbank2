<?php

class page_accounts_Loan extends Page {
	function page_index(){
		// parent::init();

		$crud=$this->add('CRUD');
		$crud->setModel('Account_Loan',array('AccountNumber','member_id','scheme_id','loanAmount','agent_id','ActiveStatus','gaurantor','gaurantorAddress','gaurantorPhNo','ModeOfOperation','loan_from_account_id','LoanInsurranceDate','LoanAgainstAccount_id','dealer_id'));
		if($crud->grid){
			$crud->grid->addPaginator(10);
			$crud->grid->addColumn('expander','edit_document');
		}

		if($form=$crud->form){
			$crud->form->addField('checkbox','LoanAgSecurity');

			$crud->form->add('Order')
						->move('LoanAgSecurity','after','LoanInsurranceDate')
						->now();


		}

		if($crud->isEditing('Add')){
			$k = 1;
			$documents=$this->add('Model_Document');
			foreach ($documents as $d) {
		    $crud->form->addField('checkbox',$documents['name']);
		    $crud->form->addField('text',$documents['name'].' '.$documents['Discription']);
			    $k++;
			}
			
		}



	}

	function page_edit_document(){
		$this->api->stickyGET('accounts_id');

		$documents=$this->add('Model_DocumentSubmitted');
		$documents->addCondition('accounts_id',$_GET['accounts_id']);

		$crud=$this->add('CRUD',array('allow_add'=>false));
		$crud->setModel($documents);
	}
}