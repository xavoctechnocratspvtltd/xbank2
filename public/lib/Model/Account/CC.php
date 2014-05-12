<?php
class Model_Account_CC extends Model_Account{

	public $transaction_deposit_type = TRA_CC_ACCOUNT_AMOUNT_DEPOSIT;	
	public $transaction_withdraw_type = TRA_CC_ACCOUNT_AMOUNT_WITHDRAWL;	
	public $default_transaction_deposit_narration = "CC Account Amount Deposit in {{AccountNumber}}";	
	public $default_transaction_withdraw_narration = "Amount withdrawl from CC Account {{AccountNumber}}";	

	function init(){
		parent::init();

		$this->addCondition('SchemeType','CC');

		$this->getElement('scheme_id')->getModel()->addCondition('SchemeType','CC');
		$this->getElement('Amount')->caption('CC Limit');

		$this->addHook('editing',$this);

		//$this->add('dynamic_model/Controller_AutoCreator');
	}

	function editing(){
		
	}

	function createNewAccount($member_id,$scheme_id,$branch_id, $AccountNumber,$otherValues=array(),$form=null){
		
		$new_account_id = parent::createNewAccount($member_id,$scheme_id,$branch_id, $AccountNumber,$otherValues,$form);
		if($this['Amount'])
			$this->doProsessingFeesTransactions();
	}

	function doProsessingFeesTransactions(){
		$processing_fee = $this->ref('scheme_id')->get('ProcessingFees') * $this['Amount'] / 100;
		$transaction = $this->add('Model_Transaction');
		
		$transaction->createNewTransaction(TRA_CC_ACCOUNT_OPEN, null, null, "CC Account Opened",null,array('reference_account_id'=>$this->id));
		$transaction->addDebitAccount($this,$processing_fee);
	
		$credit_account = $this->ref('branch_id')->get('Code') . SP . PROCESSING_FEE_RECEIVED . $this->ref('scheme_id')->get('name');		
		$transaction->addCreditAccount($credit_account,$processing_fee);

		$transaction->execute();

	}

	function withdrawl($amount,$narration=null,$accounts_to_credit=array(),$form=null){
		$ccbalance = $this['Amount'] - ($this['CurrentBalanceDr'] - $this['CurrentBalanceCr']);
		if ($ccbalance < $amount)
			throw $this->exception('Cannot withdraw more than '. $ccbalance,'ValidityCheck')->setField('amount');
		parent::withdrawl($amount,$narration,$accounts_to_credit,$form);
	}

}