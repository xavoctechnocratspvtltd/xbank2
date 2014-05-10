<?php
class Model_Account_Loan extends Model_Account{
	
	public $transaction_deposit_type = TRA_LOAN_ACCOUNT_AMOUNT_DEPOSIT;	
	public $default_transaction_deposit_narration = "Amount submited in Loan Account {{AccountNumber}}";	

	function init(){
		parent::init();

		$this->getElement('scheme_id')->getModel()->addCondition('SchemeType','Loan');
		$this->addCondition('SchemeType','Loan');

		$this->getElement('Amount')->caption('Loan Amount');
		//$this->add('dynamic_model/Controller_AutoCreator');
	}

	function createNewAccount($member_id,$scheme_id,$branch_id, $AccountNumber,$otherValues=array(),$form=null){

		$new_account_id = parent::createNewAccount($member_id,$scheme_id,$branch_id, $AccountNumber,$otherValues,$form);

		$documents=$this->add('Model_Document');
		foreach ($documents as $d) {
		 	if($form[$this->api->normalizeName($documents['name'])])
		 		$this->updateDocument($documents, $form[$this->api->normalizeName($documents['name'].' value')]);
		}

	}

	function deposit($amount,$narration=null,$accounts_to_debit=array(),$form=null){
		throw new Exception("Check For Premiums etc first", 1);
		
		parent::deposit($amount,$narration=null,$accounts_to_debit=array(),$form=null);
	}
}