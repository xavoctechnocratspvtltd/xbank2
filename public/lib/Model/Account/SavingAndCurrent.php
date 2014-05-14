<?php
class Model_Account_SavingAndCurrent extends Model_Account{
	
	public $transaction_deposit_type = TRA_SAVING_ACCOUNT_AMOUNT_DEPOSIT;	
	public $transaction_withdraw_type = TRA_SAVING_ACCOUNT_AMOUNT_WITHDRAWL;	
	public $default_transaction_deposit_narration = "Amount submited in {{SchemeType}} Account {{AccountNumber}}";	
	public $default_transaction_withdraw_narration = "Amount withdrawl from {{SchemeType}} Account {{AccountNumber}}";	


	function init(){
		parent::init();

		$this->addCondition('SchemeType','SavingAndCurrent');
		$this->getElement('scheme_id')->getModel()->addCondition('SchemeType','SavingAndCurrent');
		$this->getElement('Amount')->caption('Initial Opening Amount');
		//$this->add('dynamic_model/Controller_AutoCreator');
	}

	function createNewAccount($member_id,$scheme_id,$branch, $AccountNumber,$otherValues=array(),$form=null,$created_at=null){
		parent::createNewAccount($member_id,$scheme_id,$branch, $AccountNumber,$otherValues,$form,$created_at);
		if($this['Amount'])
			$this->deposit($this['Amount'],null,null,null,$created_at);
	}

	function withdrawl($amount,$narration=null,$accounts_to_credit=array(),$form=null,$on_date=null){
		$balance = $this->getOpeningBalance($this->api->nextDate($this->api->today));
		$balance = $balance['CR'] - $balance['DR'];
		$min_limit= $this->ref('scheme_id')->get('MinLimit');

		if($amount > ($balance - $min_limit))
			throw $this->exception('You Cannot withdraw by crossing minimum limit. ' .($balance - $min_limit),'ValidityCheck')->setField('amount');
		parent::withdrawl($amount,$narration,$accounts_to_credit,$form,$on_date);
	}

	/**
	 * [applyMonthlyInterest description]
	 * @param  MySql_Date_String  $till_date interest till date ... transaction of provided date included ie last date of any month
	 * @param  boolean $return    if set true, no changes or trnsaction will be saved to database only interest will get calculate and returned 
	 * @return number             returns interest as number if argument return is set true
	 */
	function applyHalfYearlyInterest($till_date=null,$return=false){
		if(!$till_date) $till_date = $this->api->today;
		if(!$this->loaded()) throw $this->exception('Account must be loaded to apply monthly interest');

		$this->scheme_join->addField('Interest');

		$last_interest_posting_date = $this['LastCurrentInterestUpdatedAt'];
		$current_interest = 0;

		$my_transactions = $this->ref('TransactionRow');
		$my_transactions->addCondition('created_at','>',$last_interest_posting_date);


		foreach ($my_transactions->getRows() as $t) {
			$openning_balance = $this->getOpeningBalance($this->api->nextDate($last_interest_posting_date));
			$days = $this->api->my_date_diff($t['created_at'],$last_interest_posting_date);
			$interest = round((((($openning_balance['CR']) - ($openning_balance['DR'])) > 0 ? (($openning_balance['CR']) - ($openning_balance['DR'])) : 0) * $this['Interest'] * $days['days_total'] / 36500), ROUND_TO);
			$current_interest += $interest;
			$last_interest_posting_date = $t['created_at'];
		}
		// From Last Transaction to today / month_end
		$openning_balance = $this->getOpeningBalance($this->api->nextDate($till_date));
		$days = $this->api->my_date_diff($till_date,$last_interest_posting_date);
		$interest = round((((($openning_balance['CR']) - ($openning_balance['DR'])) > 0 ? (($openning_balance['CR']) - ($openning_balance['DR'])) : 0) * $this['Interest'] * $days['days_total'] / 36500), ROUND_TO);
		$current_interest += $interest;

		if($return) return $current_interest;

		$this['LastCurrentInterestUpdatedAt'] = $till_date;
		$this['CurrentInterest'] = $current_interest;
		$this->save();

		if($current_interest == 0 ) return; //no need to save a new transaction of zero interest

		$transaction = $this->add('Model_Transaction');
		$transaction->createNewTransaction(TRA_INTEREST_POSTING_IN_CC_ACCOUNT, null, $till_date, "Interest posting in CC Account",null,array('reference_account_id'=>$this->id));

		$transaction->addCreditAccount($this->ref('branch_id')->get('Code') . SP . INTEREST_RECEIVED_ON . $this['scheme_name'], $current_interest);
		$transaction->addDebitAccount($this,$current_interest);
		$transaction->execute();
	}
}