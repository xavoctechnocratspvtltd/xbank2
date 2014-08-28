<?php

class page_transactions_fuel extends Page {
	public $title ='Fuel Expenses';
	function init(){
		parent::init();

		$form = $this->add('Form');
		
		$form->addField('Number','amount')->validateNotNull();
		$form->addField('autocomplete/Basic','amount_from_account')->setFieldHint('sdfsd')->setModel('Account','AccountNumber');
		$form->addField('Text','narration');
		$form->addSubmit('Fuel Expense');

		if($form->isSubmitted()){
			
			$account_model_temp = $this->add('Model_Account')
										->loadBy('AccountNumber',$form['amount_from_account']);

			if(!$account_model_temp->loaded())
				$form->displayError('amount','Oops');

			$account_model = $this->add('Model_Account_'.$account_model_temp->ref('scheme_id')->get('SchemeType'));
			$account_model->loadBy('AccountNumber',$form['amount_from_account']);

			try {
				$this->api->db->beginTransaction();
			    $account_model->fuel($form['amount'],$form['narration'],$form['amount_from_account'],$form);
			    $this->api->db->commit();
			} catch (Exception $e) {
			   	$this->api->db->rollBack();
			   	throw $e;
			}
			$form->js(null,$form->js()->reload())->univ()->successMessage($form['amount']."/- Fuel Expense added in " . $form['amount_from_account'])->execute();
		}
	}
}