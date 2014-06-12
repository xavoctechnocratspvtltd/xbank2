<?php
class Model_Account_SavingAndCurrent extends Model_Account{
	
	public $transaction_deposit_type = TRA_SAVING_ACCOUNT_AMOUNT_DEPOSIT;	
	public $default_transaction_deposit_narration = "Amount submited in {{SchemeType}} Account {{AccountNumber}}";	
	public $transaction_withdraw_type = TRA_SAVING_ACCOUNT_AMOUNT_WITHDRAWL;	
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
			$this->deposit($this['Amount'],null,null,null,$on_date=$created_at);
	}

	function deposit($amount,$narration=null,$accounts_to_debit=null,$form=null,$on_date=null,$in_branch=null){
		$this['CurrentInterest'] = $this['CurrentInterest'] + $this->getSavingInterest($on_date);
		$this['LastCurrentInterestUpdatedAt'] = $on_date;
		parent::deposit($amount,$narration,$accounts_to_debit,$form,$on_date,$in_branch);
	}

	function withdrawl($amount,$narration=null,$accounts_to_credit=array(),$form=null,$on_date=null){
		$balance = $this->getOpeningBalance($this->api->nextDate($on_date));
		$balance = $balance['CR'] - $balance['DR'];
		$min_limit= $this->ref('scheme_id')->get('MinLimit');

		if($amount > ($balance - $min_limit))
			throw $this->exception('You Cannot withdraw by crossing minimum limit. ' .($balance - $min_limit),'ValidityCheck')->setField('amount');
		
		$this['CurrentInterest'] = $this['CurrentInterest'] + $this->getSavingInterest($on_date);
		$this['LastCurrentInterestUpdatedAt'] = $on_date;

		parent::withdrawl($amount,$narration,$accounts_to_credit,$form,$on_date);
	}

	function getSavingInterest($on_date=null,$after_date_not_included=null,$on_amount=null, $at_interest_rate=null,$add_last_day=false){
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

		return $interest;
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

		$this['CurrentInterest'] = $this['CurrentInterest'] + $this->getSavingInterest($on_date=$till_date ,$after_date_not_included=null,$on_amount=null, $at_interest_rate=null,$add_last_day=true);
		$this['LastCurrentInterestUpdatedAt'] = $till_date;

		$current_interest = $this['CurrentInterest'];

		if($return) return $current_interest;

		$this->save();

		if($current_interest == 0 ) return; //no need to save a new transaction of zero interest

		$transaction = $this->add('Model_Transaction');
		$transaction->createNewTransaction(TRA_INTEREST_POSTING_IN_CC_ACCOUNT, null, $till_date, "Interest posting in CC Account",null,array('reference_account_id'=>$this->id));

		$transaction->addCreditAccount($this->ref('branch_id')->get('Code') . SP . INTEREST_RECEIVED_ON . $this['scheme_name'], $current_interest);
		$transaction->addDebitAccount($this,$current_interest);
		$transaction->execute();
	}
}