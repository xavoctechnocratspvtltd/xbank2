<?php

class page_accounts_DDS extends Page {
	function init(){
		parent::init();

		$this->add('Controller_Acl');
		
		$crud=$this->add('xCRUD',array('grid_class'=>'Grid_Account','add_form_beautifier'=>false));
		
		$account_dds_model = $this->add('Model_Account_DDS');
		$account_dds_model->setOrder('created_at','Desc');
		$account_dds_model->addCondition('dds_type','DDS');

		$self=$this;
		$crud->addHook('myupdate',function($crud,$form)use($self){
			if($crud->isEditing('edit')) return false;
				
				$sm_model=$self->add('Model_Account_SM');
				$sm_model->addCondition('member_id',$form['member_id']);
				$sm_model->tryLoadAny();
				if(!$sm_model->loaded()){
					$form->displayError('member',"Member Does not have SM Account");
				}

			if(!$form['sig_image_id']){
				$form->displayError('sig_image_id','Signature File is Must');
			}

			if($form['NomineeAge'] And  $form['NomineeAge']<18 And $form['MinorNomineeParentName']==""){
				$form->displayError('MinorNomineeParentName','mandatory field');
			}

			if($form['Amount']<=0){
				$form->displayError('Amount','Must be a valid Positive Number');
			}

			if($form['debit_account']){
				$debit_account = $crud->add('Model_Account');
				$debit_account->tryLoadBy('AccountNumber',$form['debit_account']);
				
				$form['debit_account'] = array(array($form['debit_account']=>$form['initial_opening_amount']));
			}


			$dds_account_model = $crud->add('Model_Account_DDS');			
			try {
				$crud->api->db->beginTransaction();
			    $dds_account_model->createNewAccount($form['member_id'],$form['scheme_id'],$crud->api->current_branch, $form['AccountNumber'],$form->getAllFields(),$form);
			    $crud->api->db->commit();
			} catch (Exception $e) {
			   	$crud->api->db->rollBack();
			   	throw $e;
			}

			$dds_account_model->callApi();
			return true;
		});

		if($crud->isEditing("add")){

		    $o=$crud->form->add('Order');
			$k = 2;
			for($k=2;$k<=4;$k++) {
			    $f=$crud->form->addField('autocomplete/Basic','member_ID_'.$k);
			   	$f->setModel('Member')->addCondition('is_active',true);
			   	$o->move($f->other_field,'before','Nominee');
			}

			$crud->form->addField('line','initial_opening_amount');
			$debit_account = $crud->form->addField('autocomplete/Basic','debit_account');
			
			$debit_account_model = $this->add('Model_Active_Account');
		
			$debit_account_model->addCondition(
					$debit_account_model->dsql()->orExpr()
						->where($debit_account_model->scheme_join->table_alias.'.name',BANK_ACCOUNTS_SCHEME)
						->where($debit_account_model->scheme_join->table_alias.'.name',BANK_OD_SCHEME)
						->where($debit_account_model->scheme_join->table_alias.'.SchemeType',ACCOUNT_TYPE_SAVING)
						->where($debit_account_model->scheme_join->table_alias.'.name',SUSPENCE_ACCOUNT_SCHEME)
						->where($debit_account_model->scheme_join->table_alias.'.name',CASH_ACCOUNT_SCHEME)

				);

			$debit_account_model->add('Controller_Acl');

			$debit_account->setModel($debit_account_model,'AccountNumber');
			$account_dds_model->getElement('member_id')->getModel()->addCondition('is_active',true);
			$account_dds_model->getElement('agent_id')->getModel()->addCondition('ActiveStatus',true);
			// $account_dds_model->getElement('mo_id')->getModel()->addCondition('is_active',true);
			$account_dds_model->getElement('team_id')->getModel()->addCondition('is_active',true);
		}

		if($crud->isEditing('edit')){
			$account_dds_model->hook('editing');
		}
		

		$crud->setModel($account_dds_model,array('AccountNumber','member_id','scheme_id','Amount','agent_id','collector_id','ActiveStatus','ModeOfOperation','Nominee','NomineeAge','MinorNomineeParentName','RelationWithNominee','team_id','sig_image_id'),array('AccountNumber','created_at','member','scheme','Amount','agent','collector','ActiveStatus','ModeOfOperation','Nominee','NomineeAge','RelationWithNominee','team'));
		$crud->addRef('JointMember');
		$crud->add('Controller_DocumentsManager',array('doc_type'=>'RDandDDSAccount'));
		
		if(!$crud->isEditing()){
			$crud->grid->addPaginator(10);
			$crud->grid->addQuickSearch(array('AccountNumber'));
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

		if($crud->isEditing('add')){
			$crud->form->getElement('member_id')->getModel()->addCondition('is_active',true);
			$crud->form->getElement('scheme_id')->getModel()->addCondition('ActiveStatus',true);
			$crud->form->getElement('scheme_id')->getModel()->putValidDateCondition();
			$crud->form->getElement('agent_id')->getModel()->addCondition('ActiveStatus',true);
			
			$o->move('initial_opening_amount','before','Amount')
				// ->move('collector_saving_account','after','collector_id')
				->now();
		}

		$crud->add('Controller_Acl');

	}
}