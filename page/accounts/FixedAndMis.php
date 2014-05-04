<?php

class page_Accounts_FixedAndMis extends Page {
	function init(){
		parent::init();

		$crud=$this->add('xCRUD');
		
		
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

		}

		$crud->setModel('Account_FixedAndMis',array('AccountNumber','member_id','scheme_id','Amount','account_to_debit_id','agent_id','ActiveStatus','ModeOfOperation','intrest_to_account_id','Nominee','NomineeAge','RelationWithNominee'));
		
		if($crud->grid)
			$crud->grid->addPaginator(10);

		if($crud->isEditing('add')){
			$o->now();
		}

	}
}