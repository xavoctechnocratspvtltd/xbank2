<?php

class page_accounts_Recurring extends Page {
	function init(){
		parent::init();
		
		$crud=$this->add('xCRUD');
		$account_recurring_model = $this->add('Model_Account_Recurring');
		$account_recurring_model->add('Controller_Acl');
		$account_recurring_model->setOrder('id','desc');
		
		$crud->addHook('myupdate',function($crud,$form){
			if($crud->isEditing('edit')) return false;
			$new_account = $crud->add('Model_Account_Recurring');
			$new_account->createNewAccount($form['member_id'],$form['scheme_id'],$crud->api->current_branch, $form['AccountNumber'],$form->getAllFields(),$form);
			return true;
		});

		if($crud->isEditing("add")){
		    $o=$crud->form->add('Order');
			$k = 2;
			for($k=2;$k<=4;$k++) {
			    $f=$crud->form->addField('autocomplete/Basic','member_ID'.$k);
			   	$f->setModel('Member');
			   	$o->move($f->other_field,'before','Nominee');
			}
			$crud->form->addField('line','initial_opening_amount');

			// $c_a_f=$crud->form->addField('autocomplete/Basic','collector_saving_account');
			// $c_a_f->setModel('Account_SavingAndCurrent');
		}

		if($crud->isEditing('edit')){
			$account_recurring_model->hook('editing');
		}

		$crud->setModel($account_recurring_model,array('AccountNumber','member','scheme','Amount','agent','ActiveStatus','collector','ModeOfOperation','Nominee','NomineeAge','RelationWithNominee'));
		
		if($crud->grid){
			$crud->grid->addPaginator(50);
			$crud->grid->addQuickSearch(array('AccountNumber','member','scheme','agent'));
			
		}

		if($form=$crud->form){
			
			$crud->form->add('Order')
						// ->move($c_a_f->other_field,'after','Amount')
						->move('initial_opening_amount','before','Amount')
						->now();


		}

		if($crud->isEditing('add')){
			$o->now();
		}


	}
}