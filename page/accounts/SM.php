<?php

// TODO :  Signature File in new account

class page_accounts_SM extends Page {
	function init(){
		parent::init();

		$crud=$this->add('xCRUD',array('grid_class'=>'Grid_Account','add_form_beautifier'=>false));
		$account_Default_model = $this->add('Model_Account_SM');
		
		$account_Default_model->add('Controller_Acl');
		$account_Default_model->setOrder('id','desc');

		$crud->addHook('myupdate',function($crud,$form){
			if($crud->isEditing('edit')) return false;
			
			$Default_account_model = $crud->add('Model_Account_SM');			
			try {
				$crud->api->db->beginTransaction();
			    $Default_account_model->createNewAccount($form['member_id'],$form['scheme_id'],$crud->api->current_branch, $Default_account_model->getNewAccountNumber() ,$form->getAllFields(),$form);
			    $Default_account_model->deposit($form['Amount'],$narration='Share Account Opened for member '. $form['member'],$accounts_to_debit=null,$form,$transaction_date=null,$in_branch=null);
			    $crud->api->db->commit();
			} catch (Exception $e) {
			   	$crud->api->db->rollBack();
			   	throw $e;
			}
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
		}

		if($crud->isEditing('edit')){
			$account_Default_model->hook('editing');
		}

		$crud->setModel($account_Default_model,array('AccountNumber1','Amount','member_id','scheme_id','Nominee','NomineeAge','MinorNomineeParentName','RelationWithNominee','ActiveStatus','sig_image_id'),array('AccountNumber','member','scheme','ActiveStatus'));
		
		if($crud->grid){
			$crud->grid->addOrder()->move('member','first')->now();
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
			$m = $crud->form->getElement('scheme_id')->getModel();
			// $m->addCondition('SchemeType',ACCOUNT_TYPE_DEFAULT);
			$m->addCondition('name','Share Capital');
			$m->addCondition('published',true);
			// $o->move('initial_opening_amount','before','Amount')
			// ->now();
		}

	}
}