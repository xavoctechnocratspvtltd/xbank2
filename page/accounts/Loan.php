<?php

class page_accounts_Loan extends Page {
	function page_index(){
		// parent::init();

		$crud=$this->add('xCRUD');
		
		
		$crud->addHook('myupdate',function($crud,$form){
			$form->js()->univ()->errorMessage($form['aaa'])->execute();
		});

		if($crud->isEditing("add")){
		    $o=$crud->form->add('Order');
			$k = 1;
			$documents=$this->add('Model_Document');
			foreach ($documents as $d) {
			    $f=$crud->form->addField('checkbox',$documents['name']);
			   	$o->move($f,'last');
			    $f=$crud->form->addField('line',$documents['name'].' '.$documents['Discription']);
			   	$o->move($f,'last');
			    $k++;
			}

			$crud->form->addField('line','aaa');			
		}

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


		if($crud->isEditing('add')){
			$o->now();
		}
		// Form Submit handler
		

	}

	function page_edit_document(){
		$this->api->stickyGET('accounts_id');

		$documents=$this->add('Model_DocumentSubmitted');
		$documents->addCondition('accounts_id',$_GET['accounts_id']);

		$crud=$this->add('CRUD',array('allow_add'=>true));
		$crud->setModel($documents);
	}
}