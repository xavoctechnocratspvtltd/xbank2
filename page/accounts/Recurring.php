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

			if(!$form['sig_image_id'])
				$form->displayError('sig_image_id','Signature File is must');

			$new_account = $crud->add('Model_Account_Recurring');
			try {
				$crud->api->db->beginTransaction();
			    $new_account->createNewAccount($form['member_id'],$form['scheme_id'],$crud->api->current_branch, $form['AccountNumber'],$form->getAllFields(),$form);
			    $crud->api->db->commit();
			} catch (Exception $e) {
			   	$crud->api->db->rollBack();
			   	throw $e;
			}
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

		$crud->setModel($account_recurring_model,array('AccountNumber','member_id','scheme_id','Amount','agent_id','ActiveStatus','collector_id','ModeOfOperation','Nominee','NomineeAge','MinorNomineeParentName','RelationWithNominee','mo_id','team_id','sig_image_id'),array('AccountNumber','member','scheme','Amount','agent','ActiveStatus','collector','ModeOfOperation','Nominee','NomineeAge','RelationWithNominee','mo','team'));
		
		if($crud->grid){
			$crud->grid->addPaginator(50);
			$crud->grid->addQuickSearch(array('AccountNumber','member','scheme','agent'));
			
			$nominee_age_field = $crud->form->getElement('NomineeAge');			
			$nominee_age_field->js(true)->univ()->bindConditionalShow(array(
						''=>array(),
						'1'=>array('MinorNomineeParentName'),
						'2'=>array('MinorNomineeParentName'),
						'3'=>array('MinorNomineeParentName'),
						'4'=>array('MinorNomineeParentName'),
						'5'=>array('MinorNomineeParentName'),
						'6'=>array('MinorNomineeParentName'),
						'7'=>array('MinorNomineeParentName'),
						'8'=>array('MinorNomineeParentName'),
						'9'=>array('MinorNomineeParentName'),
						'10'=>array('MinorNomineeParentName'),
						'11'=>array('MinorNomineeParentName'),
						'12'=>array('MinorNomineeParentName'),
						'13'=>array('MinorNomineeParentName'),
						'14'=>array('MinorNomineeParentName'),
						'15'=>array('MinorNomineeParentName'),
						'16'=>array('MinorNomineeParentName'),
						'17'=>array('MinorNomineeParentName'),
						),'div .atk-form-row');

		}

		if($form=$crud->form){
			


		}

		if($crud->isEditing('add')){
			$crud->form->add('Order')
						// ->move($c_a_f->other_field,'after','Amount')
						->move('initial_opening_amount','before','Amount')
						->now();
			$o->now();
		}


	}
}