<?php
class Model_Account_SavingAndCurrent extends Model_Account{
	
	public $transaction_deposit_type = TRA_SAVING_ACCOUNT_AMOUNT_DEPOSIT;	
	public $default_transaction_deposit_narration = "Amount submited in {{SchemeType}} Account {{AccountNumber}}";	
	public $transaction_withdraw_type = TRA_SAVING_ACCOUNT_AMOUNT_WITHDRAWL;	
	public $default_transaction_withdraw_narration = "Amount withdrawl from {{SchemeType}} Account {{AccountNumber}}";	


	function init(){
		parent::init();

		$this->getElement('account_type')->enum(array('Saving','Current'))->mandatory(true);
		$this->addCondition('SchemeType','SavingAndCurrent');
		$this->getElement('scheme_id')->getModel()->addCondition('SchemeType','SavingAndCurrent');
		$this->getElement('Amount')->caption('Initial Opening Amount');
		//$this->add('dynamic_model/Controller_AutoCreator');
	}

	function createNewAccount($member_id,$scheme_id,$branch, $AccountNumber,$otherValues=array(),$form=null,$created_at=null){
		if(!$AccountNumber) $AccountNumber = $this->getNewAccountNumber($otherValues['account_type']);
		parent::createNewAccount($member_id,$scheme_id,$branch, $AccountNumber,$otherValues,$form,$created_at);
		if($this['Amount']){
			$this->deposit($this['Amount'],null,null,null,$on_date=$created_at);


			$member=$this->add('Model_Member')->load($member_id);
			$msg="Dear Member, Your account ".$AccountNumber." has been opened with amount ". $this['Amount'] . " on dated ". $this->app->today. " From:- Bhawani Credit Co-Operative Society Ltd. +91 8003597814";
			
			$mobile_no=explode(',', $member['PhoneNos']);
			if(strlen(trim($mobile_no[0])) == 10){
				$sms=$this->add('Controller_Sms');
				$sms->sendMessage($mobile_no[0],$msg);
			}

		}
	}

	function deposit($amount,$narration=null,$accounts_to_debit=null,$form=null,$on_date=null,$in_branch=null){
		// $this['CurrentInterest'] = $this['CurrentInterest'] + $this->getSavingInterest($on_date);
		// $this['LastCurrentInterestUpdatedAt'] = $on_date;
		parent::deposit($amount,$narration,$accounts_to_debit,$form,$on_date,$in_branch);
		$this->save();
	}

	function withdrawl($amount,$narration=null,$accounts_to_credit=array(),$form=null,$on_date=null,$in_branch=null){
		$balance = $this->getOpeningBalance($this->api->nextDate($on_date));
		$balance = round($balance['CR'] - $balance['DR'],0);
		$min_limit= $this->ref('scheme_id')->get('MinLimit');

		if($amount > ($balance)){
			throw $this->exception('Cannot withdraw amount','ValidityCheck')->setField('amount');
		}
		
		// $this['CurrentInterest'] = $this['CurrentInterest'] + $this->getSavingInterest($on_date);
		// $this['LastCurrentInterestUpdatedAt'] = $on_date;

		parent::withdrawl($amount,$narration,$accounts_to_credit,$form,$on_date,$in_branch);
		$this->save();
	}

	function getSavingInterest($on_date=null,$after_date_not_included=null,$on_amount=null, $at_interest_rate=null,$add_last_day=false){
		if(!$on_date) $on_date = $this->api->today;
		if(!$after_date_not_included) $after_date_not_included = $this['LastCurrentInterestUpdatedAt']?:$this->api->nextDate($this['created_at']);
		if(!$on_amount){
			$openning_balance = $this->getOpeningBalance($this->api->nextDate($after_date_not_included));
			$on_amount = ($openning_balance['CR'] - $openning_balance['DR']) > 0 ? ($openning_balance['CR'] - $openning_balance['DR']) :0;
		}
		if(!$at_interest_rate) $at_interest_rate = $this->ref('scheme_id')->get('Interest');

		$days = $this->api->my_date_diff(date("Y-m-d",strtotime($on_date)),date("Y-m-d",strtotime($after_date_not_included)));

		// Month end or Halfyearly extra last day as there may not be any transaction on last day
		if($add_last_day) $days['days_total']++; 
		
		$interest = $on_amount * $at_interest_rate * $days['days_total'] / 36500;

		// echo $this['AccountNumber'] .' :: on-date '.$on_date . ' from date '. $after_date_not_included .' -- Op DR '. $openning_balance['DR'] .' : Op CR '.$openning_balance['CR'].' on amount '. $on_amount . ' -- @ ' . $at_interest_rate . ' -- for days '. $days['days_total'] . ' -- interest is = ' . $interest . '<br/>';
		// echo $this['AccountNumber'] .' :: on-date '.$on_date . ' from date '. $after_date_not_included .' on amount '. $on_amount . ' -- @ ' . $at_interest_rate . ' -- for days '. $days['days_total'] . ' -- interest is = ' . $interest . '<br/>';

		return round($interest,2);
	}

	/**
	 * [applyMonthlyInterest description]
	 * @param  MySql_Date_String  $till_date interest till date ... transaction of provided date included ie last date of any month
	 * @param  boolean $return    if set true, no changes or trnsaction will be saved to database only interest will get calculate and returned 
	 * @return number             returns interest as number if argument return is set true
	 */
	function applyHalfYearlyInterest($till_date=null,$return=false, $last_halfyearly_closing=null){
		
		if(!$till_date) $till_date = $this->api->today;
		if(!$this->loaded()) throw $this->exception('Account must be loaded to apply monthly interest');

		if(!$last_halfyearly_closing) $last_halfyearly_closing = $this->ref('branch_id')->ref('Closing')->tryLoadAny()->get('halfyearly');
		
		// echo $last_halfyearly_closing . "<br/>";
		// $after_date_not_included = $this['LastCurrentInterestUpdatedAt'];
		if(strtotime($this['created_at']) > strtotime($last_halfyearly_closing)){
			$after_date_not_included = $this->api->nextDate($this['created_at']);
		}else{
			$after_date_not_included = $this->api->nextDate($last_halfyearly_closing);
		}

		$openning_balance = $this->getOpeningBalance($after_date_not_included);
		$on_amount = ($openning_balance['CR'] - $openning_balance['DR']) > 0 ? ($openning_balance['CR'] - $openning_balance['DR']) :0;

		// echo "on_amount $on_amount <br/>";
		// echo "after_date_not_included $after_date_not_included <br/>";

		$current_interest=0;
		$trans = $this->add('Model_TransactionRow')->addCondition('account_id',$this->id);
		$trans->addCondition('created_at','>=',$after_date_not_included);
		$trans->setOrder('created_at');
		
		foreach ($trans as $junk) {
			if(!isset($last_transaction_date) or $last_transaction_date !== date('Y-m-d',strtotime($trans['created_at']))){
				$current_interest += $this->getSavingInterest($trans['created_at'],$after_date_not_included,$on_amount,null, false);
			}

			if($trans['amountDr']!=0) $on_amount -= $trans['amountDr'];
			if($trans['amountCr']!=0) $on_amount += $trans['amountCr'];
			$after_date_not_included = $trans['created_at'];
			$last_transaction_date = date("Y-m-d",strtotime($trans['created_at']));
		}


		$current_interest +=  $this->getSavingInterest($on_date=$till_date ,$after_date_not_included ,$on_amount, $at_interest_rate=null,$add_last_day=true);
		// $this['LastCurrentInterestUpdatedAt'] = $this->api->nextDate($till_date);

		// $current_interest = $this['CurrentInterest'];

		// echo $this['AccountNumber']. " " . $current_interest . "<br/>";
		
		if($current_interest < 0 ) $current_interest = 0;
		
		if($return) return $current_interest;

		$this->save();

		if($current_interest == 0 ) 
			return; //no need to save a new transaction of zero interest

		$current_interest = round($current_interest,2);

		$transaction = $this->add('Model_Transaction');
		$transaction->createNewTransaction(TRA_INTEREST_POSTING_IN_SAVINGS, $this->ref('branch_id'), $till_date, "Interest posting in Saving Account",null,array('reference_id'=>$this->id));

		$transaction->addCreditAccount($this,$current_interest);
		$transaction->addDebitAccount($this->ref('branch_id')->get('Code') . SP . INTEREST_PAID_ON . SP. $this['scheme_name'], $current_interest);
		$transaction->execute();

		$transaction=null;
		unset($transaction);

		$trans = null;
		unset($trans);
	}

	function isMinBalanceChargeAppliedInThisQuarter($on_date=null){
		if(!$on_date) $on_date = $this->api->now;

		$qrtr = $this->api->getFinancialQuarter($on_date);

		$transaction_type_model = $this->add('Model_TransactionType');
		$transaction_type_model->tryLoadBy('name',TRA_MINIMUM_BALANCE_CHARGES);
		
		if(!$transaction_type_model->loaded()) $transaction_type_model->save();

		$tr = $this->add('Model_TransactionRow');
		$tr->addCondition('account_id',$this->id);
		$tr->addCondition('created_at','>=',$qrtr['start_date']);
		$tr->addCondition('created_at','<',$this->api->nextDate($qrtr['end_date']));
		$tr->addCondition('transaction_type_id',$transaction_type_model->id);

		return $tr->tryLoadAny()->loaded();


	}

	function applyMinBalanceCharge($on_date=null){
		$transaction = $this->add('Model_Transaction');
		$transaction->createNewTransaction(TRA_MINIMUM_BALANCE_CHARGES, $this->ref('branch_id'), $on_date, 'Min Balance Charge REceived on '. $this['AccountNumber'], null, array('reference_id'=>$this->id));
		
		$charge = $this->getMinimumBalanceCharge();

		$transaction->addDebitAccount($this, $charge);
		$transaction->addCreditAccount($this['branch_code'].SP.MINIMUM_BALANCE_CHARGE_RECEIVED_ON.SP.$this['scheme_name'], $charge);
		
		$transaction->execute();
	}

	function getMinimumBalanceCharge(){
		return MIN_BALANCE_CHARGE;
	}
}