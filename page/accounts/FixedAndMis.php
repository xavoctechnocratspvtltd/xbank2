<?php

class page_Accounts_FixedAndMis extends Page {
	function init(){
		parent::init();

		$crud=$this->add('xCRUD');
		$account_fixedandmis_model = $this->add('Model_Account_FixedAndMis');
		$account_fixedandmis_model->add('Controller_Acl');
		$account_fixedandmis_model->setOrder('id','desc');
		
		$crud->addHook('myupdate',function($crud,$form){
			if($crud->isEditing('edit')) return false;
			
			$fixedAndMis_account_model = $crud->add('Model_Account_FixedAndMis');
			$fixedAndMis_account_model->createNewAccount($form['member_id'],$form['scheme_id'],$crud->api->current_branch, $form['AccountNumber'],$form->getAllFields(),$form);
			return true;
		});

		if($crud->isEditing("add")){
		    $o=$crud->form->add('Order');
			for($k=2;$k<=4;$k++) {
			    $f = $crud->form->addField('autocomplete/Basic','member_ID_'.$k);
			   	$f->setModel('Member');
			   	$o->move($f->other_field,'last');
			}
			$debit_account = $crud->form->addField('autocomplete/Basic','debit_account');
			$debit_account->setModel('Account','AccountNumber');

		}

		if($crud->isEditing('edit')){
			$account_fixedandmis_model->hook('editing');
		}

		$crud->setModel($account_fixedandmis_model,array('account_type','AccountNumber','member_id','scheme_id','Amount','agent_id','ActiveStatus','ModeOfOperation','intrest_to_account_id','MaturityToAccount_id','Nominee','NomineeAge','RelationWithNominee','mo_id','team_id'));

		if($crud->isEditing()){
			$type_field = $crud->form->getElement('account_type');
			$type_field->js(true)->univ()->bindConditionalShow(array(
					''=>array(),
					'FD'=>array('MaturityToAccount_id'),
					'MIS'=>array('intrest_to_account_id')
					),'div .atk-form-row');
			$crud->form->getElement('account_type')->setEmptyText('Please Select');
		}

		if($crud->isEditing('add')){
			$o->now();
		}

		if($crud->grid){
			$crud->grid->addPaginator(10);
			$crud->grid->addQuickSearch(array('AccountNumber'));
		}
	}
}