<?php
class Model_Account_SavingAndCurrent extends Model_Account{
	
	public $transaction_deposit_type = TRA_SAVING_ACCOUNT_AMOUNT_DEPOSIT;	
	public $default_transaction_deposit_narration = "Amount submited in Saving Account {{AccountNumber}}";	

	function init(){
		parent::init();

		$this->addCondition('SchemeType','SavingAndCurrent');
		$this->getElement('scheme_id')->getModel()->addCondition('SchemeType','SavingAndCurrent');
		$this->getElement('Amount')->caption('Initial Opening Amount');
		//$this->add('dynamic_model/Controller_AutoCreator');
	}
}