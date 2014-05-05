<?php

class page_transactions_deposit extends Page {
	public $title ='Deposit Amount';

	function init(){
		parent::init();

		$form = $this->add('Form');
		$form->addField('autocomplete/Basic',array('name'=>'account'))->validateNotNull()->setModel('Account','AccountNumber');
		$form->addField('Number','amount')->validateNotNull();
		$form->addField('autocomplete/Basic','account_to_debit')->setFieldHint('sdfsd')->setModel('Account');
		$form->addField('Text','narration');
		$form->addSubmit('Deposit');

		if($form->isSubmitted()){
			$form->js()->univ()->successMessage($form['account'])->execute();
			$account_model_temp = $this->add('Model_Account')->load($form['account']);
			$account_model = $this->add('Model_Account_'.$account_model_temp->ref('scheme_id')->get('SchemeType'));
			$account_model->deposit($form['amount'],$form['narration'],$form['account_to_debit']?array($form['account_to_debit']=>$form['amount']):array(),$form);
			$form->js()->univ()->successMessage($form['account'])->execute();
		}
	}
}