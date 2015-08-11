<?php
class Model_Account_CC extends Model_Account{

	public $transaction_deposit_type = TRA_CC_ACCOUNT_AMOUNT_DEPOSIT;	
	public $transaction_withdraw_type = TRA_CC_ACCOUNT_AMOUNT_WITHDRAWL;	
	public $default_transaction_deposit_narration = "CC Account Amount Deposit in {{AccountNumber}}";	
	public $default_transaction_withdraw_narration = "Amount withdrawl from CC Account {{AccountNumber}}";	

	function init(){
		parent::init();

		$this->addCondition('SchemeType','CC');

		$this->getElement('agent_id')->destroy();
		$this->getElement('collector_id')->destroy();
		$this->getElement('scheme_id')->getModel()->addCondition('SchemeType','CC');
		$this->getElement('Amount')->caption('CC Limit');
		$this->getElement('account_type')->defaultValue(ACCOUNT_TYPE_CC);

		$this->addHook('editing',$this);

		//$this->add('dynamic_model/Controller_AutoCreator');
	}

	function editing(){
		
	}

	function createNewAccount($member_id,$scheme_id,$branch_id, $AccountNumber=null,$otherValues=array(),$form=null,$on_date=null){
		if(!$AccountNumber) $AccountNumber = $this->getNewAccountNumber();
		$new_account_id = parent::createNewAccount($member_id,$scheme_id,$branch_id, $AccountNumber,$otherValues,$form,$on_date);
		if($this['Amount'])
			$this->doProsessingFeesTransactions($on_date);
	}

	function doProsessingFeesTransactions($on_date=null){
		if(!$on_date) $on_date = $this->api->now;

		$processing_fee = $this->ref('scheme_id')->get('ProcessingFees') * $this['Amount'] / 100;
		$transaction = $this->add('Model_Transaction');
		
		$transaction->createNewTransaction(TRA_CC_ACCOUNT_OPEN, null, $on_date, "CC Account Opened",null,array('reference_id'=>$this->id));
		$transaction->addDebitAccount($this,$processing_fee);
	
		$credit_account = $this->ref('branch_id')->get('Code') . SP . PROCESSING_FEE_RECEIVED . SP. $this->ref('scheme_id')->get('name');		
		$transaction->addCreditAccount($credit_account,$processing_fee);

		$transaction->execute();

	}

	function deposit($amount,$narration=null,$accounts_to_debit=null,$form=null,$on_date=null,$transaction_in_branch=null){
		if(!$transaction_in_branch) $transaction_in_branch = $this->api->current_branch;
		if(!$on_date) $on_date = $this->api->now;
		$this['CurrentInterest'] = $this['CurrentInterest'] + $this->getCCInterest($on_date);
		$this['LastCurrentInterestUpdatedAt'] = $on_date;
		$this->save();
		parent::deposit($amount,$narration,$accounts_to_debit,$form,$on_date,$transaction_in_branch);
	}

	function withdrawl($amount,$narration=null,$accounts_to_credit=null,$form=null,$on_date=null,$transaction_in_branch=null){
		if(!$transaction_in_branch) $transaction_in_branch = $this->api->current_branch;
		if(!$on_date) $on_date = $this->api->now;
		$ccbalance = $this['Amount'] /* This is CC LIMIT actually in Amount*/ - ($this['CurrentBalanceDr'] - $this['CurrentBalanceCr']);
		if (round($ccbalance,0) < round($amount,0))
			throw $this->exception('Cannot withdraw more than '. $ccbalance,'ValidityCheck')->setField('amount');

		$this['CurrentInterest'] = $this['CurrentInterest'] + $this->getCCInterest($on_date);
		$this['LastCurrentInterestUpdatedAt'] = $on_date;
		$this->save();
		parent::withdrawl($amount,$narration,$accounts_to_credit,$form,$on_date,$transaction_in_branch);
	}

	function getCCInterest($on_date=null,$after_date_not_included=null,$on_amount=null, $at_interest_rate=null,$add_last_day=false){
		if(!$on_date) $on_date = $this->api->today;
		if(!$after_date_not_included) $after_date_not_included = $this['LastCurrentInterestUpdatedAt'];
		
		if(!$on_amount){
			$openning_balance = $this->getOpeningBalance($this->api->nextDate($after_date_not_included));
			$on_amount = ($openning_balance['DR'] - $openning_balance['CR']) > 0 ? ($openning_balance['DR'] - $openning_balance['CR']) :0;
		}
		
		if(!$at_interest_rate) $at_interest_rate = $this->ref('scheme_id')->get('Interest');

		$days = $this->api->my_date_diff($on_date,$after_date_not_included);

		if($add_last_day) $days['days_total']++;
		
		$interest = $on_amount * $at_interest_rate * $days['days_total'] / 36500;

		// echo $this['AccountNumber'] .' :: on-date '.$on_date . ' -- Op DR '. $openning_balance['DR'] .' : Op CR '.$openning_balance['CR'].' on amount '. $on_amount . ' -- @ ' . $at_interest_rate . ' -- for days '. $days['days_total'] . ' -- interest is = ' . $interest . '<br/>';
		// throw new \Exception("Error Processing Request", 1);
		
		return $interest;
	}

	/**
	 * [applyMonthlyInterest description]
	 * @param  MySql_Date_String  $on_date interest till date ... transaction of provided date included ie last date of any month
	 * @param  boolean $return    if set true, no changes or trnsaction will be saved to database only interest will get calculate and returned 
	 * @return number             returns interest as number if argument return is set true
	 */
	function postInterestEntry($on_date=null, $return=false, $branch=null){
		if(!$on_date) $on_date = $this->api->today;
		if(!$this->loaded()) throw $this->exception('Account must be loaded to apply monthly interest');
		if(!$branch) $branch = $this->ref('branch_id');

		// Interest from last transaction to month end
		$current_interest = $this['CurrentInterest'] + $this->getCCInterest($on_date,$after_date_not_included=null,$on_amount=null, $at_interest_rate=null,$add_last_day=true);
		$this['CurrentInterest']=0; // Make Zero to be ready for next months Interests
		$this['LastCurrentInterestUpdatedAt'] = $on_date;

		if($return) return $current_interest;
		
		$this->save();

		if($current_interest == 0 ) return; //no need to save a new transaction of zero interest

		$transaction = $this->add('Model_Transaction');
		$transaction->createNewTransaction(TRA_INTEREST_POSTING_IN_CC_ACCOUNT, $branch, $on_date, "Interest posting in CC Account",null,array('reference_id'=>$this->id));

		$transaction->addCreditAccount($this->ref('branch_id')->get('Code') . SP . INTEREST_RECEIVED_ON . SP . $this['scheme_name'], $current_interest);
		$transaction->addDebitAccount($this,$current_interest);
		$transaction->execute();
	}
}