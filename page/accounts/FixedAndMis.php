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
			try {
				$crud->api->db->beginTransaction();

			    	$fixedAndMis_account_model->createNewAccount($form['member_id'],$form['scheme_id'],$crud->api->current_branch,null,$form->getAllFields(),$form);
			    $crud->api->db->commit();
			}catch(Exception_ValidityCheck $e){
				$crud->api->db->rollBack();
				if($form->hasElement($e->getField()))
				   	$form->displayError($e->getField(),$e->getMessage());
				  else
				  	$form->js()->univ()->errorMessage($e->getMessage())->execute();
			}catch (Exception $e) {
			   	$crud->api->db->rollBack();
			   	throw $e;
			}
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



		}

		if($crud->isEditing('edit')){
			$account_fixedandmis_model->hook('editing');
		}

		$crud->setModel($account_fixedandmis_model,array('account_type','AccountNumber','member_id','scheme_id','Amount','agent_id','ActiveStatus','ModeOfOperation','intrest_to_account_id','MaturityToAccount_id','Nominee','NomineeAge','RelationWithNominee','mo_id','team_id','sig_image_id'),array('account_type','AccountNumber','member','scheme','Amount','agent','ActiveStatus','ModeOfOperation','intrest_to_account','MaturityToAccount','Nominee','NomineeAge','RelationWithNominee','mo','team'));

		if($crud->isEditing()){
			if($crud->form->hasElement('account_type')){
				$type_field = $crud->form->getElement('account_type');
				$type_field->js(true)->univ()->bindConditionalShow(array(
						''=>array(),
						'FD'=>array('MaturityToAccount_id'),
						'MIS'=>array('intrest_to_account_id')
						),'div .atk-form-row');
				$crud->form->getElement('account_type')->setEmptyText('Please Select');
			}
		}

		if($crud->isEditing('add')){

			$member_field  = $crud->form->getElement('member_id');
			$amount_field = $crud->form->getElement('Amount');
			$pan_details = $amount_field->belowField()->add('View');

			if($_GET['member_id_for_pan']){
				$member_model_for_pan =$this->add('Model_Member');
				$member_model_for_pan->load($_GET['member_id_for_pan']);
				if($_GET['amount_filled'] >=50000 and (strlen($member_model_for_pan['PanNo']) != 10) ){
					$pan_details->setHTML('<font color="red">No Pan Card Found</font>');
				}elseif($_GET['amount_filled'] < 50000 and strlen($member_model_for_pan['PanNo']) != 10){
					$pan_details->set('Pan Card Not Found, But not needed');
				}else{
					$pan_details->set('Pan Card Found');
				}
				return;
			}

			$amount_field->js('change',$pan_details->js()->reload(array('amount_filled'=>$amount_field->js()->val(),'member_id_for_pan'=>$member_field->js()->val())));
			
			$o->now();
		}

		if($crud->grid){
			$crud->grid->addPaginator(10);
			$crud->grid->addQuickSearch(array('AccountNumber','agent'));
		}
	}
}