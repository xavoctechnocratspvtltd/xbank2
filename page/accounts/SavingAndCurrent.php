<?php

class page_accounts_SavingAndCurrent extends Page {
	function init(){
		parent::init();

		$crud=$this->add('xCRUD');
		$account_savingandcurrent_mdoel = $this->add('Model_Account_SavingAndCurrent');
		$account_savingandcurrent_mdoel->add('Controller_Acl');
		
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

		$crud->setModel($account_savingandcurrent_mdoel,array('AccountNumber','member_id','scheme_id','Amount','agent_id','ActiveStatus','ModeOfOperation','Nominee','NomineeAge','RelationWithNominee'));
		
		if($crud->grid)
			$crud->grid->addPaginator(10);

		if($crud->isEditing('add')){
			$o->now();
		}

	}
}