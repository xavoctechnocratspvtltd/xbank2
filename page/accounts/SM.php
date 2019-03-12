<?php

// TODO :  Signature File in new account

class page_accounts_SM extends Page {
	function init(){
		parent::init();

		$this->app->stickyGET('selected_member_id');

		$this->add('Controller_Acl');
		
		$crud=$this->add('xCRUD',array('grid_class'=>'Grid_Account','add_form_beautifier'=>false));
		$account_Default_model = $this->add('Model_Account_SM');

		$account_Default_model->addExpression('father_name')->set($account_Default_model->refSQL('member_id')->fieldQuery('FatherName'));
		
		$account_Default_model->add('Controller_Acl');
		$account_Default_model->setOrder('id','desc');

		$crud->addHook('myupdate',function($crud,$form){
			if($crud->isEditing('edit')) return false;
			
			$Default_account_model = $crud->add('Model_Account_SM');

			if($form['Amount'] < RATE_PER_SHARE || $form['Amount'] % RATE_PER_SHARE !=0) {
				throw $this->exception('Amount must be multiple of '.RATE_PER_SHARE,'ValidityCheck')->setField('Amount');
			}
			
			// check here validation either pancard or form 60/61 is required
			$member_model = $this->add('Model_Member')->load($form['member_id']);
			
			if(empty($member_model['PanNo']) && !$form['form_60_61_is_submitted'] && !$member_model->form60IsSubmitted()){
				throw $this->exception('either PanCard No or Form 60/61 is required','ValidityCheck')->setField('form_60_61_is_submitted');
			}
			
			try {
				$crud->api->db->beginTransaction();
			    $id = $Default_account_model->createNewAccount($form['member_id'],$form['scheme_id'],$crud->api->current_branch, $Default_account_model->getNewAccountNumber() ,$form->getAllFields(),$form);
			    $Default_account_model->deposit($form['Amount'],$narration='Share Account Opened for member '. $form['member'],$form['debit_account']?[ [ $form['debit_account']=>$form['Amount'] ] ]:null,$form,$transaction_date=null,$in_branch=null);
				
				// submit form 60/61
				$form60_model = $member_model->form60IsSubmitted('model');

				if(!$form60_model->loaded() && $form['form_60_61_is_submitted']){
					$member_model->submitForm60(null,$Default_account_model->id);
				}elseif(!$form60_model['accounts_id'] && $form['form_60_61_is_submitted']){
					$form60_model['accounts_id'] = $Default_account_model->id;
					$form60_model->save();
				}
				
			    $crud->api->db->commit();
			} catch(Exception_ValidityCheck $e){
			} catch (Exception $e) {
			   	$crud->api->db->rollBack();
			   	throw $e;
			}

			$Default_account_model->callApi();

			return true;
		});

		if($crud->isEditing("add")){
		    $o=$crud->form->add('Order');
			// $k = 2;
			// for($k=2;$k<=4;$k++) {
			//     $f=$crud->form->addField('autocomplete/Basic','member_ID'.$k);
			//    	$f->setModel('Member');
			//    	$o->move($f->other_field,'before','Nominee');
			// }
		    $account_Default_model->getElement('member_id')->getModel()->addCondition('is_active',true);

		    $debit_account = $crud->form->addField('autocomplete/Basic','debit_account');
			
			$debit_account_model = $this->add('Model_Active_Account');
		
			$debit_account_model->addCondition(
					$debit_account_model->dsql()->orExpr()
						->where($debit_account_model->scheme_join->table_alias.'.name',BANK_ACCOUNTS_SCHEME)
						->where($debit_account_model->scheme_join->table_alias.'.name',BANK_OD_SCHEME)
						->where($debit_account_model->scheme_join->table_alias.'.SchemeType',ACCOUNT_TYPE_SAVING)
						->where($debit_account_model->scheme_join->table_alias.'.name',SUSPENCE_ACCOUNT_SCHEME)
						->where($debit_account_model->scheme_join->table_alias.'.name',CASH_ACCOUNT_SCHEME)
						->where($debit_account_model->table_alias.'.AccountNumber',$this->app->current_branch['Code'].SP.'MEMBERSHIP ADVANCE AMOUNT')

				);

			// $debit_account_model->add('Controller_Acl');

			$debit_account->setModel($debit_account_model,'AccountNumber');

			$form = $crud->form;
			$form->addField('checkbox','form_60_61_is_submitted');
			$o->move('form_60_61_is_submitted','last');
		}

		if($crud->isEditing('edit')){
			$account_Default_model->hook('editing');
		}

		$crud->setModel($account_Default_model,array('AccountNumber','Amount','member_id','scheme_id','Nominee','NomineeAge','MinorNomineeParentName','RelationWithNominee','ActiveStatus','sig_image_id'),array('AccountNumber','created_at','member','father_name','branch','scheme','ActiveStatus'));
		
		if(!$crud->isEditing()){
			// $crud->grid->addOrder()->move('member','first')->now();
			$crud->grid->addQuickSearch(array('AccountNumber'));
		}

		if($crud->isEditing()){
			$member_model = $crud->form->getElement('member_id')->getModel();
			$member_model->addExpression('existing_sm_count')->set(function($m,$q){
				return $m->refSQL('Account')->addCondition('AccountNumber','Like','SM%')->count();
			});
			$member_model->addCondition('existing_sm_count',0);

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
			$member_field = $crud->form->getElement('member_id');
			$member_field->getModel()->addCondition('is_active',true);

			$form_60_61_is_submitted_field = $crud->form->getElement('form_60_61_is_submitted');
			$member_field->other_field->js('change',$crud->form->js()->atk4_form('reloadField','form_60_61_is_submitted',array($this->api->url(),'selected_member_id'=>$member_field->js()->val())));

			if($mid = $_GET['selected_member_id']){
				$m_model = $this->add('Model_Member')->load($mid);
				$submit_form_60_model = $m_model->form60IsSubmitted('model');

				if($submit_form_60_model->loaded()){
					$form_60_61_is_submitted_field->set($submit_form_60_model->loaded());
					// $form_60_61_is_submitted_field->setAttr('disabled','disabled');
					$form_60_61_is_submitted_field->afterField()->add('View')->set('Submitted On = '.$submit_form_60_model['submitted_on']." with account = ".$submit_form_60_model['accounts']);

					// $form_60_61_description_field->set($submit_form_60_model['description'].", Submitted on = ".$submit_form_60_model['submitted_on']);
					// $form_60_61_description_field->setAttr('disabled','disabled');
				}

			}

			$m = $crud->form->getElement('scheme_id')->getModel();
			// $m->addCondition('SchemeType',ACCOUNT_TYPE_DEFAULT);
			$m->addCondition('name','Share Capital');
			$m->addCondition('published',true);
			$m->putValidDateCondition();
			// $o->move('initial_opening_amount','before','Amount');
			$o->now();
		}
		$crud->add('Controller_Acl');
	}
}