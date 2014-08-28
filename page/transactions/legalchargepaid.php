<?php

class page_transactions_legalchargepaid extends Page {
	public $title ='Legal Charge Paid';
	function init(){
		parent::init();

		$form = $this->add('Form');
		
		$form->addField('Number','amount')->validateNotNull();
		$form->addField('autocomplete/Basic','amount_from_account')->setFieldHint('sdfsd')->setModel('Account','AccountNumber');
		$form->addField('Text','narration');
		$form->addSubmit('Legal Charge Paid');

		if($form->isSubmitted()){
			
			$account_model_temp = $this->add('Model_Account')
										->loadBy('AccountNumber',$form['amount_from_account']);

			if(!$account_model_temp->loaded())
				$form->displayError('amount','Oops');

			$account_model = $this->add('Model_Account_'.$account_model_temp->ref('scheme_id')->get('SchemeType'));
			$account_model->loadBy('AccountNumber',$form['amount_from_account']);

			try {
				$this->api->db->beginTransaction();
			    $account_model->legalChargePaid($form['amount'],$form['narration'],$form['amount_from_account'],$form);
			    $this->api->db->commit();
			} catch (Exception $e) {
			   	$this->api->db->rollBack();
			   	throw $e;
			}
			$form->js(null,$form->js()->reload())->univ()->successMessage($form['amount']."/- Legal Charge Paid in " . $form['amount_from_account'])->execute();
		}
	}
}