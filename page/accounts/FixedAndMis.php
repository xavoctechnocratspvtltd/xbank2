<?php

class page_Accounts_FixedAndMis extends Page {
	function init(){
		parent::init();

		$crud=$this->add('xCRUD');
		$account_fixedandmis_model = $this->add('Model_Account_FixedAndMis');
		$account_fixedandmis_model->add('Controller_Acl');
		
		$crud->addHook('myupdate',function($crud,$form){
			$form->js()->univ()->errorMessage($form['aaa'])->execute();
		});

		if($crud->isEditing("add")){
		    $o=$crud->form->add('Order');
			for($k=2;$k<=4;$k++) {
			    $f = $crud->form->addField('autocomplete/Basic','member_ID_'.$k);
			   	$f->setModel('Member');
			   	$o->move($f->other_field,'last');
			}
		}

		$crud->setModel($account_fixedandmis_model,array('AccountNumber','member_id','scheme_id','Amount','agent_id','ActiveStatus','ModeOfOperation','intrest_to_account_id','Nominee','NomineeAge','RelationWithNominee'));
		
		if($crud->isEditing('add')){
			$o->now();
		}

		if($crud->grid)
			$crud->grid->addPaginator(10);


	}
}