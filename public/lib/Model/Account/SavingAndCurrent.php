<?php
class Model_Account_SavingAndCurrent extends Model_Account{
	
	public $transaction_deposit_type = TRA_SAVING_ACCOUNT_AMOUNT_DEPOSIT;	
	public $default_transaction_deposit_narration = "Amount submited in Saving Account {{AccountNumber}}";	
	public $default_transaction_deposit_narration = "CC Account Amount Deposit in {{AccountNumber}}";	
	public $default_transaction_withdraw_narration = "Amount withdrawl from CC Account {{AccountNumber}}";	


	function init(){
		parent::init();

		$this->addCondition('SchemeType','SavingAndCurrent');
		$this->getElement('scheme_id')->getModel()->addCondition('SchemeType','SavingAndCurrent');
		$this->getElement('Amount')->caption('Initial Opening Amount');
		//$this->add('dynamic_model/Controller_AutoCreator');
	}

	function withdrawl($amount,$narration=null,$accounts_to_credit=array(),$form=null,$on_date=null){
		if($amount > ($min_limit= $this->ref('scheme_id')->get('MinLimit')))
			throw $this->exception('You Cannot withdraw by crossing minimum limit','ValidityCheck')->setField('amount');
		parent::withdrawl($amount,$narration,$accounts_to_credit,$form,$on_date);
	}
}