<?php
class Model_Account_Recurring extends Model_Account{
	
	public $transaction_deposit_type = TRA_RECURRING_ACCOUNT_AMOUNT_DEPOSIT;	
	public $default_transaction_deposit_narration = "Recurring Amount Deposit in {{AccountNumber}}";	

	function init(){
		parent::init();

		$this->addCondition('SchemeType','Recurring');
		$this->getElement('scheme_id')->getModel()->addCondition('SchemeType','Recurring');
		$this->getElement('Amount')->caption('RECURRING amount (premium)');
		//$this->add('dynamic_model/Controller_AutoCreator');
	}

	function deposit($amount,$narration=null,$accounts_to_debit=array(),$form=null){
		throw new Exception("Check For Premiums and commissions etc first", 1);
		
		parent::deposit($amount,$narration=null,$accounts_to_debit=array(),$form=null);
	}
}