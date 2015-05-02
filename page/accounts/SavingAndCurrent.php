<?php

class page_accounts_SavingAndCurrent extends Page {
	function init(){
		parent::init();

		$this->add('Controller_Acl');

		$crud=$this->add('xCRUD',array('grid_class'=>'Grid_Account','add_form_beautifier'=>false));
		$account_savingandcurrent_model = $this->add('Model_Account_SavingAndCurrent');
		$account_savingandcurrent_model->add('Controller_Acl');
		$account_savingandcurrent_model->setOrder('id','desc');
		
		$crud->addHook('myupdate',function($crud,$form){
			if($crud->isEditing('edit')) return false;
			$sbca_account_model = $crud->add('Model_Account_SavingAndCurrent');
			
			if(!$form['sig_image_id']){
				$form->displayError('sig_image_id','Signature File is must to provide');
			}

			try {
				$crud->api->db->beginTransaction();
			    	$sbca_account_model->createNewAccount($form['member_id'],$form['scheme_id'],$crud->api->current_branch, $form['AccountNumber'],$form->getAllFields(),$form);
			    $crud->api->db->commit();
			} catch (Exception $e) {
			   	$crud->api->db->rollBack();
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

			$account_savingandcurrent_model->getElement('member_id')->getModel()->addCondition('is_active',true);

		}

		if($crud->isEditing('edit')){
			$account_savingandcurrent_model->hook('editing');
		}

		$crud->setModel($account_savingandcurrent_model,array('account_type','AccountNumber','member_id','scheme_id','Amount','agent_id','ActiveStatus','ModeOfOperation','Nominee','NomineeAge','MinorNomineeParentName','RelationWithNominee','mo_id','team_id','sig_image_id'),array('AccountNumber','created_at','member','scheme','Amount','agent','ActiveStatus','ModeOfOperation','Nominee','NomineeAge','RelationWithNominee','CurrentInterest','LastCurrentInterestUpdatedAt','mo','team'));
		
		if($crud->isEditing()){

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

		if(!$crud->isEditing()){
			$crud->grid->addPaginator(50);
			$crud->grid->addQuickSearch(array('AccountNumber','member_id','agent','scheme_id','agent_id','amount','mo_id','team_id'));
		}

		if($crud->isEditing('add')){
			$crud->form->getElement('scheme_id')->getModel()->addCondition('ActiveStatus',true);
			$crud->form->getElement('account_type')->setEmptyText('Please Select');
			$o->now();
		}

		$crud->add('Controller_Acl');

	}
}