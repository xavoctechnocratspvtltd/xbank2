<?php

class page_Accounts_FixedAndMis extends Page {
	function init(){
		parent::init();

		$this->app->stickyGET('selected_member_id');

		$this->add('Controller_Acl');

		$crud=$this->add('xCRUD',array('grid_class'=>'Grid_Account','add_form_beautifier'=>false));
		$account_fixedandmis_model = $this->add('Model_Account_FixedAndMis');
		$account_fixedandmis_model->add('Controller_Acl');
		$account_fixedandmis_model->setOrder('id','desc');
		$self=$this;
		$crud->addHook('myupdate',function($crud,$form)use($self){
			if($crud->isEditing('edit')) return false;
			
			$fixedAndMis_account_model = $crud->add('Model_Account_FixedAndMis');
			$sm_model=$self->add('Model_Account_SM');
				$sm_model->addCondition('member_id',$form['member_id']);
				$sm_model->tryLoadAny();
				if(!$sm_model->loaded()){
					$form->displayError('member',"Member Does not have SM Account");
				}
			
			// check here validation either pancard or form 60/61 is required
			$member_model = $this->add('Model_Member')->load($form['member_id']);
			if(empty($member_model['PanNo']) && !$form['form_60_61_is_submitted'] && !$member_model->form60IsSubmitted()){
				throw $this->exception('either PanCard No or Form 60/61 is required','ValidityCheck')->setField('form_60_61_is_submitted');
			}

			try {
				$crud->api->db->beginTransaction();

					if($form['account_type']=='MIS' and !$form['intrest_to_account_id']){
						$form->displayError('intrest_to_account_id','Field is must for MIS type accounts');
					}

					if(!$form['debit_account']) $form->displayError('debit_account','Please specify account');

					$account = $crud->add('Model_Account');
					$account->loadBy('AccountNumber',$form['debit_account']);

					$member_ids_filled=[];
					$member_ids_filled[] = $form['member_id'];
					
					if($form['member_ID_2']) $member_ids_filled[] =$form['member_ID_2'];
					if($form['member_ID_3']) $member_ids_filled[] =$form['member_ID_3'];
					if($form['member_ID_4']) $member_ids_filled[] =$form['member_ID_4'];

					$member_ids_of_saving_account = [$account['member_id']];

					foreach ($account->ref('JointMember') as $jm) {
						$member_ids_of_saving_account[] =$jm['member_id'];
					}

					if($account['SchemeType']==ACCOUNT_TYPE_SAVING and !count(array_intersect($member_ids_of_saving_account, $member_ids_filled))){
						$form->displayError('debit_account','Account must be of same member');
					}

					$intrest_to_account_model = $crud->add('Model_Account');
					$intrest_to_account_model->addCondition('id',$form['intrest_to_account_id']);
					$intrest_to_account_model->tryLoadAny();
					if($form['intrest_to_account_id'] AND $intrest_to_account_model['member_id'] != $form['member_id']){
						$form->displayError('intrest_to_account_id','Not for same member ');
					}
					if($form['NomineeAge'] And  $form['NomineeAge']<18 And $form['MinorNomineeParentName']==""){
						$form->displayError('MinorNomineeParentName','mandatory field');
					}
					if($form['MaturityToAccount_id']){
						$maturity_to_account_model = $crud->add('Model_Account');
						$maturity_to_account_model->addCondition('id',$form['MaturityToAccount_id']);
						$maturity_to_account_model->tryLoadAny();
						if($maturity_to_account_model['member_id'] != $form['member_id']){
							$form->displayError('MaturityToAccount','Not for same member ');
						}

					}

					$fixedAndMis_account_model->allow_any_name = true;
			    	$fixedAndMis_account_model->createNewAccount($form['member_id'],$form['scheme_id'],$crud->api->current_branch,null,$form->getAllFields(),$form);
			    	
			    	// submit form 60/61
					$form60_model = $member_model->form60IsSubmitted('model');
					if(!$form60_model->loaded() && $form['form_60_61_is_submitted']){
						$member_model->submitForm60(null,$fixedAndMis_account_model->id);
					}elseif(!$form60_model['accounts_id'] && $form['form_60_61_is_submitted']){
						$form60_model['accounts_id'] = $fixedAndMis_account_model->id;
						$form60_model->save();
					}

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

			$fixedAndMis_account_model->callApi();

			return true;
		});

		if($crud->isEditing("add")){
		    $o=$crud->form->add('Order');
			for($k=2;$k<=4;$k++) {
			    $f = $crud->form->addField('autocomplete/Basic','member_ID_'.$k);
			   	$f->setModel('Member');
			   	// $o->move($f->other_field,'last');
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

			// $debit_account_model->add('Controller_Acl');

			$debit_account->setModel($debit_account_model,'AccountNumber');
			$account_fixedandmis_model->getElement('member_id')->getModel()->addCondition('is_active',true);
			// $account_fixedandmis_model->getElement('mo_id')->getModel()->addCondition('is_active',true);
			$account_fixedandmis_model->getElement('team_id')->getModel()->addCondition('is_active',true);

			$form = $crud->form;
			$form->addField('checkbox','form_60_61_is_submitted');
			$o->move('form_60_61_is_submitted','last');
		}

		if($crud->isEditing('edit')){
			$account_fixedandmis_model->hook('editing');
		}

		$crud->setModel($account_fixedandmis_model,array('account_type','AccountNumber','member_id','scheme_id','Amount','agent_id','collector_id','team_id','ActiveStatus','ModeOfOperation','intrest_to_account_id','MaturityToAccount_id','Nominee','RelationWithNominee','NomineeAge','MinorNomineeDOB','MinorNomineeParentName','sig_image_id','new_or_renew'),array('AccountNumber','created_at','member','scheme','Amount','agent','collector','ActiveStatus','ModeOfOperation','intrest_to_account','MaturityToAccount','Nominee','NomineeAge','RelationWithNominee','team','new_or_renew'));
		$crud->addRef('JointMember');
		$crud->add('Controller_DocumentsManager',array('doc_type'=>'FixedMISAccount'));
		
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

			$intrest_to_account_model = $crud->form->getElement('intrest_to_account_id')->getModel();
			$intrest_to_account_model->addCondition('SchemeType',ACCOUNT_TYPE_SAVING);

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
			for($k=2;$k<=4;$k++) {
			    $f = $crud->form->getElement('member_ID_'.$k);
			   	$o->move($f->other_field,'before','intrest_to_account_id');
			}

			$type_field = $crud->form->getElement('ModeOfOperation');
				$type_field->js(true)->univ()->bindConditionalShow(array(
						'Self'=>array('Nominee','NomineeAge','RelationWithNominee','MinorNomineeDOB'),
						'Joint'=>array('member_ID_1','member_ID_2','member_ID_3','member_ID_4'),
						),'div .atk-form-row');

			// $o->move($debit_account,'last');
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

		if(!$crud->isEditing()){
			$crud->grid->addPaginator(50);
			$crud->grid->addQuickSearch(array('AccountNumber'));
		}

		$crud->add('Controller_Acl');

		$crud->grid->add('VirtualPage')
			->addColumn('renew','Renew','Renew')
			->set([$this,'renew']);
	}

	function renew($page){
		$id = $_GET[$page->short_name.'_id'];
		$financial_year = $page->app->getFinancialYear();
		$start_date = $financial_year['start_date'];
		$end_date = $financial_year['end_date'];
		// $end_date = $this->app->nextDate($end_date);

		$account_fixedandmis_model = $this->add('Model_Account_FixedAndMis');
		$account_fixedandmis_model->load($id);
		
		$start_time = strtotime($start_date);
		$end_time = strtotime($end_date);
		$maturity_time = strtotime($account_fixedandmis_model['maturity_date']);

		if(!($maturity_time >= $start_time && $maturity_time <= $end_time ) ){
			$page->add('View_Warning')->setHtml("Account is not matured in current financial_year Start Date: ".$financial_year['start_date']." End Date: ".$financial_year['end_date']." maturity_date: ".$account_fixedandmis_model['maturity_date']);
			return;
		}

		$balance = $account_fixedandmis_model->getOpeningBalance($this->app->nextDate($this->app->today));
		$op_balance = $balance['cr'] - $balance['dr'];
		
		if(!$account_fixedandmis_model['ActiveStatus'] || $op_balance <= 0 || $account_fixedandmis_model->isLocked() ||!$account_fixedandmis_model->isMatured() ){
			$page->add('View_Warning')->setHtml("<br/>
					Account ".$account_fixedandmis_model['name']." not Renew, possible reasons are <br/>".
					"<br/> Account Status: ".($account_fixedandmis_model['ActiveStatus']?'Active':'DeActive').
					"<br/> Balance: ".$op_balance.
					"<br/> Is Locked: ".($account_fixedandmis_model->isLocked()?"Yes, First Final SL Loan then Renew Again":"No".
					"<br/> Account Matured: ".($account_fixedandmis_model->isMatured()?"Yes":"No"))
				);
			return;
		}

		// $account_fixedandmis_model->add('Controller_Acl');

		$form_fields = ['account_type','debit_account_id','member_id','scheme_id','Amount','agent_id','team_id','ModeOfOperation','intrest_to_account_id','MaturityToAccount_id','Nominee','RelationWithNominee','NomineeAge','MinorNomineeDOB','MinorNomineeParentName','sig_image_id','new_or_renew'];
		$copy_fields = ['account_type','member_id','agent_id','team_id','ModeOfOperation','intrest_to_account_id','MaturityToAccount_id','Nominee','RelationWithNominee','NomineeAge','MinorNomineeDOB','MinorNomineeParentName','sig_image_id'];
		$read_only_fields = ['account_type','debit_account','member_id','ModeOfOperation','intrest_to_account_id','new_or_renew','Amount'];
		// if($account_fixedandmis_model['account_type'])
		// $page->add('View')->set("Account Type ".$account_fixedandmis_model['account_type']);
		$renew_model = $this->add('Model_Account_FixedAndMis');
		$renew_model->getElement('scheme_id')->getModel()->addCondition('ActiveStatus',true);

		$renew_form = $page->add('Form',null,null,['form/stacked']);
		$debit_account = $renew_form->addField('Readonly','debit_account');
		$debit_account_model = $this->add('Model_Active_Account');
		// $debit_account_model->addCondition(
		// 		$debit_account_model->dsql()->orExpr()
		// 			->where($debit_account_model->scheme_join->table_alias.'.name',BANK_ACCOUNTS_SCHEME)
		// 			->where($debit_account_model->scheme_join->table_alias.'.name',BANK_OD_SCHEME)
		// 			->where($debit_account_model->scheme_join->table_alias.'.SchemeType',ACCOUNT_TYPE_SAVING)
		// 			->where($debit_account_model->scheme_join->table_alias.'.name',SUSPENCE_ACCOUNT_SCHEME)
		// 			->where($debit_account_model->scheme_join->table_alias.'.name',CASH_ACCOUNT_SCHEME)
		// 	);
		$debit_account->setModel($debit_account_model,'AccountNumber');

		$renew_form->setModel($renew_model,$form_fields);
		$renew_form->addField('checkbox','form_60_61_is_submitted');

		foreach ($copy_fields as $key => $field_name){
			$renew_form->getElement($field_name)->set($account_fixedandmis_model[$field_name]);
		}

		$renew_form->getElement('new_or_renew')->set('ReNew');
		$renew_form->getElement('Amount')->set($op_balance);
		$renew_form->getElement('debit_account')->set($account_fixedandmis_model['AccountNumber']);


		foreach ($read_only_fields as $key => $field_name){
			$field = $renew_form->getElement($field_name);
			if(in_array($field_name, ['member_id','intrest_to_account_id']))
				$field->other_field->disable()->validate();
			else
				$field->disable()->validate();
		}

		$renew_form->addSubmit('ReNew Now')->addClass('atk-button atk-swatch-yellow atk-box atk-padding');

		if($renew_form->isSubmitted()){
			
			$fixedAndMis_account_model = $page->add('Model_Account_FixedAndMis');

			$sm_model = $this->add('Model_Account_SM');
			$sm_model->addCondition('member_id',$account_fixedandmis_model['member_id']);
			$sm_model->tryLoadAny();
			if(!$sm_model->loaded()){
				$renew_form->displayError('member_id',"Member Does not have SM Account");
			}
			
			// check here validation either pancard or form 60/61 is required
			$member_model = $this->add('Model_Member')->load($account_fixedandmis_model['member_id']);
			if(empty($member_model['PanNo']) && !$renew_form['form_60_61_is_submitted'] && !$member_model->form60IsSubmitted()){
				$renew_form->displayError('form_60_61_is_submitted',"either PanCard No or Form 60/61 is required");
			}

			if($account_fixedandmis_model['account_type']=='MIS' and !$account_fixedandmis_model['intrest_to_account_id']){
				$renew_form->displayError('intrest_to_account_id','Field is must for MIS type accounts');
			}
			
			if($renew_form['NomineeAge'] And  $renew_form['NomineeAge']<18 And $renew_form['MinorNomineeParentName']==""){
				$renew_form->displayError('MinorNomineeParentName','mandatory field');
			}

			try {
				
				$renew_form->api->db->beginTransaction();

				if($renew_form['MaturityToAccount_id']){
					$maturity_to_account_model = $crud->add('Model_Account');
					$maturity_to_account_model->addCondition('id',$renew_form['MaturityToAccount_id']);
					$maturity_to_account_model->tryLoadAny();
					if($maturity_to_account_model['member_id'] != $account_fixedandmis_model['member_id']){
						$renew_form->displayError('MaturityToAccount_id','Not for same member ');
					}
				}

				$form_data = $renew_form->getAllFields();
				$form_data['account_type'] = $account_fixedandmis_model['account_type'];
				$form_data['Amount'] = $op_balance;
				$form_data['ModeOfOperation'] = $account_fixedandmis_model['ModeOfOperation'];
				$form_data['intrest_to_account_id'] = $account_fixedandmis_model['intrest_to_account_id'];
				$form_data['new_or_renew'] = 'ReNew';

				$fixedAndMis_account_model->allow_any_name = true;
			    $fixedAndMis_account_model->createNewAccount($member_model->id,$renew_form['scheme_id'],$renew_form->api->current_branch,null,$form_data,$renew_form);

		    	// submit form 60/61
				$form60_model = $member_model->form60IsSubmitted('model');
				if(!$form60_model->loaded() && $renew_form['form_60_61_is_submitted']){
					$member_model->submitForm60(null,$fixedAndMis_account_model->id);
				}elseif(!$form60_model['accounts_id'] && $renew_form['form_60_61_is_submitted']){
					$form60_model['accounts_id'] = $fixedAndMis_account_model->id;
					$form60_model->save();
				}

				// old fd deactivated
				$account_fixedandmis_model['ActiveStatus'] = false;
				$account_fixedandmis_model->save();

			    $renew_form->api->db->commit();
			}catch(Exception_ValidityCheck $e){
				$renew_form->api->db->rollBack();
				if($renew_form->hasElement($e->getField()))
				   	$renew_form->displayError($e->getField(),$e->getMessage());
				  else
				  	$renew_form->js()->univ()->errorMessage($e->getMessage())->execute();
			}catch (Exception $e) {
			   	$renew_form->api->db->rollBack();
			   	throw $e;
			}

			$fixedAndMis_account_model->callApi();

			$renew_form->js(null,[$renew_form->js()->trigger('reload')])->univ()->successMessage('Account Renew Successfully ')->closeDialog()->execute();
		}

	}

}