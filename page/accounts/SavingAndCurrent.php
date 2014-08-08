<?php

class page_accounts_SavingAndCurrent extends Page {
	function init(){
		parent::init();

		$crud=$this->add('xCRUD');
		$account_savingandcurrent_model = $this->add('Model_Account_SavingAndCurrent');
		$account_savingandcurrent_model->add('Controller_Acl');
		$account_savingandcurrent_model->setOrder('id','desc');
		
		$crud->addHook('myupdate',function($crud,$form){
			if($crud->isEditing('edit')) return false;
			$sbca_account_model = $crud->add('Model_Account_SavingAndCurrent');
			
			try {
				$this->api->db->beginTransaction();
			    $sbca_account_model->createNewAccount($form['member_id'],$form['scheme_id'],$crud->api->current_branch, $form['AccountNumber'],$form->getAllFields(),$form);
			    $this->api->db->commit();
			} catch (Exception $e) {
			   	$this->api->db->rollBack();
			   	throw $e;
			}
			return true;
		});


		if($crud->isEditing("add")){
		    $o=$crud->form->add('Order');
			for($k=2;$k<=4;$k++) {
			    $f=$crud->form->addField('autocomplete/Basic','member_ID'.$k, 'Joint Member '.$k);
			   	$f->setModel('Member');
			   	$o->move($f->other_field,'before','Nominee');
			}

		}

		if($crud->isEditing('edit')){
			$account_savingandcurrent_model->hook('editing');
		}

		$crud->setModel($account_savingandcurrent_model,array('account_type','AccountNumber','member_id','scheme_id','Amount','agent_id','ActiveStatus','ModeOfOperation','Nominee','NomineeAge','RelationWithNominee','mo_id','team_id'),array('account_type','AccountNumber','member','scheme','Amount','agent','ActiveStatus','ModeOfOperation','Nominee','NomineeAge','RelationWithNominee','CurrentInterest','LastCurrentInterestUpdatedAt','mo','team'));
		
		if($crud->isEditing()){
			$crud->form->getElement('account_type')->setEmptyText('Please Select');
		}

		if($crud->grid){
			$crud->grid->addPaginator(50);
			$crud->grid->addQuickSearch(array('AccountNumber','member_id','scheme_id','agent_id','amount','mo_id','team_id'));
		}

		if($crud->isEditing('add')){
			$o->now();
		}

	}
}