<?php

class page_accounts_SavingAndCurrent extends Page {
	function init(){
		parent::init();

		$this->add('Controller_Acl');
		$this->app->stickyGET('selected_member_id');

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

			// check here validation either pancard or form 60/61 is required
			$member_model = $this->add('Model_Member')->load($form['member_id']);
			if(empty($member_model['PanNo']) && !$form['form_60_61_is_submitted'] && !$member_model->form60IsSubmitted()){
				throw $this->exception('either PanCard No or Form 60/61 is required','ValidityCheck')->setField('form_60_61_is_submitted');
			}

			try {
				$crud->api->db->beginTransaction();
			    	$sbca_account_model->createNewAccount($form['member_id'],$form['scheme_id'],$crud->api->current_branch, $form['AccountNumber'],$form->getAllFields(),$form);
					
					// submit form 60/61
					$form60_model = $member_model->form60IsSubmitted('model');
					if(!$form60_model->loaded() && $form['form_60_61_is_submitted']){
						$member_model->submitForm60(null,$sbca_account_model->id);
					}elseif(!$form60_model['accounts_id'] && $form['form_60_61_is_submitted']){
						$form60_model['accounts_id'] = $sbca_account_model->id;
						$form60_model->save();
					}

			    $crud->api->db->commit();
			} catch (Exception $e) {
			   	$crud->api->db->rollBack();
			   	throw $e;
			}

			$sbca_account_model->callApi();

			return true;
		});


		if($crud->isEditing("add")){
		    $o=$crud->form->add('Order');
			for($k=2;$k<=4;$k++) {
			    $f=$crud->form->addField('autocomplete/Basic','member_ID_'.$k, 'Joint Member '.$k);
			   	$f->setModel('Member')->addCondition('is_active',true);
			   	$o->move($f->other_field,'before','Nominee');
			}

			$account_savingandcurrent_model->getElement('member_id')->getModel()->addCondition('is_active',true);

			$form = $crud->form;
			$form->addField('checkbox','form_60_61_is_submitted');
			$o->move('form_60_61_is_submitted','last');
		}

		if($crud->isEditing('edit')){
			$account_savingandcurrent_model->hook('editing');
		}

		$crud->setModel($account_savingandcurrent_model,array('account_type','AccountNumber','member_id','scheme_id','Amount','agent_id','ActiveStatus','ModeOfOperation','Nominee','NomineeAge','MinorNomineeParentName','RelationWithNominee','team_id','sig_image_id'),array('AccountNumber','created_at','member','scheme','Amount','agent','ActiveStatus','ModeOfOperation','Nominee','NomineeAge','RelationWithNominee','CurrentInterest','LastCurrentInterestUpdatedAt','team'));
		$crud->addRef('JointMember');
		$crud->add('Controller_DocumentsManager',array('doc_type'=>'SavingAccount'));
		
		// if($crud->isEditing()){

		// 	$nominee_age_field = $crud->form->getElement('NomineeAge');			
		// 	$nominee_age_field->js(true)->univ()->bindConditionalShow(array(
		// 				''=>array(),
		// 				'1'=>array('MinorNomineeParentName'),
		// 				'2'=>array('MinorNomineeParentName'),
		// 				'3'=>array('MinorNomineeParentName'),
		// 				'4'=>array('MinorNomineeParentName'),
		// 				'5'=>array('MinorNomineeParentName'),
		// 				'6'=>array('MinorNomineeParentName'),
		// 				'7'=>array('MinorNomineeParentName'),
		// 				'8'=>array('MinorNomineeParentName'),
		// 				'9'=>array('MinorNomineeParentName'),
		// 				'10'=>array('MinorNomineeParentName'),
		// 				'11'=>array('MinorNomineeParentName'),
		// 				'12'=>array('MinorNomineeParentName'),
		// 				'13'=>array('MinorNomineeParentName'),
		// 				'14'=>array('MinorNomineeParentName'),
		// 				'15'=>array('MinorNomineeParentName'),
		// 				'16'=>array('MinorNomineeParentName'),
		// 				'17'=>array('MinorNomineeParentName'),
		// 				),'div .atk-form-row');
		// }

		if(!$crud->isEditing()){
			$crud->grid->addPaginator(50);
			$crud->grid->addQuickSearch(array('AccountNumber','member_id','agent','scheme_id','agent_id','amount','team_id'));
		}

		if($crud->isEditing('add')){
			$crud->form->getElement('member_id')->getModel()->addCondition('is_active',true);
			$crud->form->getElement('scheme_id')->getModel()->addCondition('ActiveStatus',true);
			$crud->form->getElement('scheme_id')->getModel()->putValidDateCondition();
			$crud->form->getElement('account_type')->setEmptyText('Please Select');
			$o->now();

			$member_field = $crud->form->getElement('member_id');
			$form_60_61_is_submitted_field = $crud->form->getElement('form_60_61_is_submitted');
			$member_field->other_field->js('change',$crud->form->js()->atk4_form('reloadField','form_60_61_is_submitted',array($this->api->url(),'selected_member_id'=>$member_field->js()->val())));

			if($mid = $_GET['selected_member_id']){
				$m_model = $this->add('Model_Member')->load($mid);
				$submit_form_60_model = $m_model->form60IsSubmitted('model');

				if($submit_form_60_model->loaded()){
					$form_60_61_is_submitted_field->set($submit_form_60_model->loaded());
					$form_60_61_is_submitted_field->afterField()->add('View')->set('Submitted On = '.$submit_form_60_model['submitted_on']." with account = ".$submit_form_60_model['accounts']);
				}

			}
		}

		$crud->add('Controller_Acl');

	}
}