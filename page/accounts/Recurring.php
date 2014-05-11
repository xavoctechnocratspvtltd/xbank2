<?php

class page_accounts_Recurring extends Page {
	function init(){
		parent::init();
		
		$crud=$this->add('xCRUD');
		$account_recurring_model = $this->add('Model_Account_Recurring');
		$account_recurring_model->add('Controller_Acl');
		
		$crud->addHook('myupdate',function($crud,$form){
			$form->js()->univ()->errorMessage($form['aaa'])->execute();
		});

		if($crud->isEditing("add")){
		    $o=$crud->form->add('Order');
			$k = 2;
			for($k=2;$k<=4;$k++) {
			    $f=$crud->form->addField('autocomplete/Basic','member_ID'.$k);
			   	$f->setModel('Member');
			   	$o->move($f,'before','Nominee');
			}

			$f=$crud->form->addField('autocomplete/Basic','collector_saving_account');
			$f->setModel('Account_SavingAndCurrent');
		}

		if($crud->isEditing('edit')){
			$account_recurring_model->hook('editing');
		}

		$crud->setModel($account_recurring_model,array('AccountNumber','member_id','scheme_id','Amount','agent_id','ActiveStatus','collector_id','ModeOfOperation','Nominee','NomineeAge','RelationWithNominee'));
		
		if($crud->grid)
			$crud->grid->addPaginator(10);

		if($form=$crud->form){
			$crud->form->addField('line','initial_opening_amount');
			
			$crud->form->add('Order')
						->move('initial_opening_amount','before','Amount')
						->now();


		}

		if($crud->isEditing('add')){
			$o->now();
		}


	}
}