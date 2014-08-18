<?php

class page_transactions_conveyance extends Page {
	public $title ='Conveynace Transaction';
	function init(){
		parent::init();

		$form = $this->add('Form');
		$form->addField('autocomplete/Basic',array('name'=>'staff'))->validateNotNull()->setModel('Staff');
		$form->addField('Number','amount')->validateNotNull();
		$form->addField('autocomplete/Basic','account_to_debit')->setFieldHint('sdfsd')->setModel('Account','AccountNumber');
		$form->addField('Text','narration');
		$form->addSubmit('Deposit');

		if($form->isSubmitted()){
			
			$account_model_temp = $this->add('Model_Account')
										->loadBy('AccountNumber',$form['account']);

			if(!$account_model_temp->loaded())
				$form->displayError('amount','Oops');

			$account_model = $this->add('Model_Account_'.$account_model_temp->ref('scheme_id')->get('SchemeType'));
			$account_model->loadBy('AccountNumber',$form['account']);

			try {
				$this->api->db->beginTransaction();
			    $account_model->conveyance($form['staff'],$form['amount'],$form['narration'],$form['account_to_debit']?array(array($form['account_to_debit']=>$form['amount'])):array(),$form);
			    $this->api->db->commit();
			} catch (Exception $e) {
			   	$this->api->db->rollBack();
			   	throw $e;
			}
			$form->js(null,$form->js()->reload())->univ()->successMessage($form['amount']."/- deposited in " . $form['account'])->execute();
		}
	}
}