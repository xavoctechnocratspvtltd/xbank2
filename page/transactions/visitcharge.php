<?php

class page_transactions_visitcharge extends Page {
	
	public $title ='Visit Charge Application';

	function init(){
		parent::init();

		$this->add('Controller_Acl');

		$account_from_account_model = $this->add('Model_Active_Account',array('table_alias'=>'acc'));
		$account_from_account_model->addCondition('SchemeType','<>',ACCOUNT_TYPE_RECURRING);
		$account_from_account_model->addCondition('SchemeType','<>',ACCOUNT_TYPE_DDS);
		$account_from_account_model->addCondition('SchemeType','<>',ACCOUNT_TYPE_FIXED);

		$account_from_account_model->add('Controller_Acl');

		$form = $this->add('Form');
		
		$form->addField('Number','amount')->validateNotNull();
		$amount_from_account_field = $form->addField('autocomplete/Basic','amount_from_account');
		$hint_view = $amount_from_account_field->other_field->belowField()->add('View');

		$amount_from_account_field->setModel($account_from_account_model,'AccountNumber');

		if($_GET['check_cr']){
			$acc_bal_temp = $this->add('Model_Account');
			$acc_bal_temp->tryLoadBy('AccountNumber',$_GET['AccountNumber']);

			if($acc_bal_temp->loaded()){
				$bal = $acc_bal_temp->getOpeningBalance();
				$bal_cr = $bal['Dr'] - $bal['Cr'];
				if($bal_cr < 0 ) $amount_field_view->set('Already Cr Balance');
			}
		}

		$amount_from_account_field->js('change',$hint_view->js()->reload(array('check_cr'=>1,'AccountNumber'=>$amount_from_account_field->js()->val())));

		$form->addField('Text','narration');
		$form->addSubmit('Visit Charge');

		if($form->isSubmitted()){
			
			$account_model_temp = $this->add('Model_Account')
										->loadBy('AccountNumber',$form['amount_from_account']);

			if(!$account_model_temp->loaded())
				$form->displayError('amount','Oops');

			$account_model = $this->add('Model_Account_'.$account_model_temp->ref('scheme_id')->get('SchemeType'));
			$account_model->loadBy('AccountNumber',$form['amount_from_account']);

			try {
				$this->api->db->beginTransaction();
			    $account_model->visitCharge($form['amount'],$form['narration'],$form['amount_from_account'],$form);
			    $this->api->db->commit();
			} catch (Exception $e) {
			   	$this->api->db->rollBack();
			   	throw $e;
			}
			$form->js(null,$form->js()->reload())->univ()->successMessage($form['amount']."/- Vist Charge added in " . $form['amount_from_account'])->execute();
		}
	}
}