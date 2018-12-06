<?php
class Model_Account_FixedAndMis extends Model_Account{
	
	public $transaction_deposit_type = TRA_FIXED_ACCOUNT_DEPOSIT;	
	public $default_transaction_deposit_narration = "Amount submited in FD Account {{AccountNumber}}";	

	public $transaction_withdraw_type = TRA_FD_ACCOUNT_AMOUNT_WITHDRAWL;	
	public $default_transaction_withdraw_narration = "Amount withdrawl from {{SchemeType}} Account {{AccountNumber}}";

	function init(){
		parent::init();

		$this->getElement('account_type')->enum(array('FD','MIS'))->mandatory(true);
		$this->addCondition('SchemeType',ACCOUNT_TYPE_FIXED);

		$this->getElement('Amount')->caption('FD/MIS Amount');
		$this->getElement('AccountDisplayName')->caption('Account Name (IF Joint)');
		$this->getElement('scheme_id')->getModel()->addCondition('SchemeType',ACCOUNT_TYPE_FIXED);

		$this->addExpression('maturity_date')->set(function($m,$q){
			return "DATE_ADD(DATE(".$m->dsql()->getField('created_at')."), INTERVAL +".$m->scheme_join->table_alias.".MaturityPeriod DAY)";
		});

		$this->addExpression('locked_to_loan_till')->set(function($m,$q){
			return "DATE_ADD(DATE(".$m->dsql()->getField('created_at')."), INTERVAL +".$m->scheme_join->table_alias.".no_loan_on_deposit_till DAY)";
		});

		$this->scheme_join->addField('percent_loan_on_deposit');
		$this->scheme_join->addField('no_loan_on_deposit_till');

		// $this->addHook('afterAccountDebited,afterAccountCredited',array($this,'closeIfPaidCompletely'));

		// $this->scheme_join->addField('Interest');

		//$this->add('dynamic_model/Controller_AutoCreator');
	}

	function withdrawl($amount,$narration=null,$accounts_to_credit=array(),$form=null,$on_date=null,$in_branch=null){
		if(!$on_date) $on_date = $this->api->today;


		$balance = $this->getOpeningBalance($this->api->nextDate($on_date));
		$balance = round($balance['CR'] - $balance['DR'],0);
		$min_limit= 0; // No minimum limit for FD .. Let all money be withdrawn

		if(round($amount,0) > (round($balance,0))){
			throw $this->exception('Cannot withdraw amount more then '. $balance ,'ValidityCheck')->setField('amount')->addMoreInfo('Account',$this['AccountNumber']);
		}
		
		// $this['CurrentInterest'] = $this['CurrentInterest'] + $this->getSavingInterest($on_date);
		// $this['LastCurrentInterestUpdatedAt'] = $on_date;

		parent::withdrawl($amount,$narration,$accounts_to_credit,$form,$on_date,$in_branch);
		// $this->save();
	}

	function createNewAccount($member_id,$scheme_id,$branch, $AccountNumber=null,$otherValues=null,$form=null,$created_at=null){
		if(!$AccountNumber) $AccountNumber = $this->getNewAccountNumber($otherValues['account_type']);

		$scheme = $this->add('Model_Scheme')->load($scheme_id);
		
		if($scheme['MinLimit']){
			if($otherValues['Amount']< $scheme['MinLimit'])
				throw $this->exception('Scheme Minimum Limit is set as '.$scheme['MinLimit'], 'ValidityCheck')->setField('Amount');
		}

		parent::createNewAccount($member_id,$scheme_id,$branch, $AccountNumber,$otherValues,$form,$created_at);
		$this->createInitialTransaction($created_at, $form);
		if($form['agent_id']){
			$this->giveAgentCommission($created_at);
			$this->agent()->addCRPB($this->scheme()->get('CRPB'),$this['Amount']);
		}

		$member=$this->add('Model_Member')->load($member_id);
		$msg="Dear Member, Your account ".$AccountNumber." has been opened with amount ".$this['Amount']." on dated ". $this->app->today. " From:- Bhawani Credit Co-Operative Society Ltd. +91 8003597814";
		
		$mobile_no=explode(',', $member['PhoneNos']);
		if(strlen(trim($mobile_no[0])) == 10){
			$sms=$this->add('Controller_Sms');
			$sms->sendMessage($mobile_no[0],$msg);
		}

	}

	function createInitialTransaction($on_date, $form){

		$transaction = $this->add('Model_Transaction');
		$transaction->createNewTransaction(TRA_FIXED_ACCOUNT_DEPOSIT, $this->ref('branch_id'), $on_date, "Initial Fixed Amount Deposit in ".$this['AccountNumber'], $only_transaction=null, array('reference_id'=>$this->id));
		
		if($form['debit_account']){
			$db_acc = $this->add('Model_Account',['with_balance_cr'=>true,'with_balance_dr'=>true])
				->setActualFields(['balance_cr','balance_dr','SchemeType'])
				->loadBy('AccountNumber',$form['debit_account']);
			$credit_balance = $db_acc['balance_cr'];
			$debit_balance = $db_acc['balance_dr'];	
			$compare_with = $this['Amount'];
			$error_field='Amount';

			$is_saving_account = $db_acc['SchemeType'] == 'SavingAndCurrent';

			if($is_saving_account){
				if($credit_balance < $compare_with) throw $this->exception('Insufficient Amount, Current Balance Cr.'. $credit_balance,'ValidityCheck')->setField($error_field);
			}

			$debit_account = $form['debit_account'];
		}else{
			$debit_account = $this->ref('branch_id')->get('Code').SP.CASH_ACCOUNT;
		}
		
		$transaction->addDebitAccount($debit_account, $this['Amount']);
		$transaction->addCreditAccount($this, $this['Amount']);
		
		$transaction->execute();
	}

	function giveAgentCommission($on_date=null){
		// TODO :: To give commission .... 
		if(!$on_date) $on_date= $this->api->today;

		$commissionAmount = $this->api->getComission($this->ref('scheme_id')->get('AccountOpenningCommission'), OPENNING_COMMISSION);
        $commissionAmount = $commissionAmount * $this["Amount"] / 100.00;


        $commissionForThisAgent = $this->agent()->cadre()->selfEfectivePercentage() * $commissionAmount / 100.00;

        $transaction = $this->add('Model_Transaction');
        $transaction->createNewTransaction(TRA_ACCOUNT_OPEN_AGENT_COMMISSION, $this->ref('branch_id'), $on_date, "Agent Account openning commision for ".$this['AccountNumber'], $only_transaction=null, array('reference_id'=>$this->id));
        
        $transaction->addDebitAccount($this['branch_code'] . SP . COMMISSION_PAID_ON . SP. $this['scheme_name'], $commissionForThisAgent);

        $agent_saving_account = $this->ref('agent_id')->ref('account_id');
        $tds_account = $this->add('Model_Account')->loadBy('AccountNumber',$this['branch_code'].SP.BRANCH_TDS_ACCOUNT);

        $tds_amount = round((strlen($agent_saving_account->ref('member_id')->get('PanNo'))==10)? $commissionForThisAgent * TDS_PERCENTAGE_WITH_PAN /100 : $commissionForThisAgent * TDS_PERCENTAGE_WITHOUT_PAN /100,2);
		
		$saving_amount = $commissionForThisAgent - $tds_amount;        

        $transaction->addCreditAccount($agent_saving_account, $saving_amount);
        $transaction->addCreditAccount($tds_account, $tds_amount);
        
        $transaction_id = $transaction->execute();

        $this->add('Model_AgentTDS')
			->createNewEntry($this['agent_id'],$transaction_id,$this->id,$commissionForThisAgent,$tds_amount,$commissionForThisAgent - $tds_amount);

        $this->propogateAgentCommission($debit_acocunt = $this['branch_code'] . SP . COMMISSION_PAID_ON . SP. $this['scheme_name'], $total_commission_amount = $commissionAmount, $on_date, $this->id);


	}

	// function getFDMISInterest($on_date){
	// 	// (a.CurrentInterest + (a.CurrentBalanceCr * $sc->Interest * DATEDIFF('" . $i . "', a.LastCurrentInterestUpdatedAt)/36500)
	// 	$days = $this->api->my_date_diff($on_date, $this['LastCurrentInterestUpdatedAt']);
	// 	return $this['CurrentInterest'] + ($this['CurrentBalanceCr'] * $this['Interest'] * $days['days_total'] / 36500);
	// }

	function getAmountForInterest($on_date=null,$calculate_remainig_days=true, $interest_rate_provided = null){
		
		if(!$on_date) $on_date = $this->api->today;

		$on_amount = $this['Amount'];

		$days = $this->api->my_date_diff($on_date,date("Y-m-d",strtotime($this['created_at'])));

		$years_completed = (int) (($days['days_total']) / 365) ;
		$remaining_days = ($days['days_total'] % 365) ;
		
		if(!$interest_rate_provided){
			$interest_rate = $this['Interest'];
			if(!$interest_rate) $interest_rate = $this->ref('scheme_id')->get('Interest');
		}else{
			$interest_rate = $interest_rate_provided;
		}

		for ($i=0; $i < $years_completed; $i++) { 
			$interest = $on_amount * $interest_rate / 100;
			$on_amount += $interest;
		}

		if($remaining_days and $calculate_remainig_days){
			$interest = $on_amount * $interest_rate / 36500 * ($remaining_days);
			$on_amount += $interest;
		}

		// echo "doing provision on $on_amount on $on_date for ".$days['days_total']." days and years completed = $years_completed <br/>";
		return $on_amount;
	}

	function doInterestProvision($on_date,$maturity_day=false){
		// a.CurrentInterest=(a.CurrentBalanceCr * s.Interest * DATEDIFF('" . getNow("Y-m-d") . "', a.LastCurrentInterestUpdatedAt)/36500), a.LastCurrentInterestUpdatedAt='" . getNow("Y-m-d") . "' WHERE
		if(!$this->loaded()) throw $this->exception('Account Must be loaded to post Interest in it');

		$days = $this->api->my_date_diff($on_date,$this['LastCurrentInterestUpdatedAt']);

		// Count One more day when FD is openned
		if(strtotime(date('Y-m-d',strtotime($this['LastCurrentInterestUpdatedAt']))) == strtotime(date('Y-m-d',strtotime($this['created_at'])))) 
			$days['days_total']+=2;
		// Deduct One Day from last day of maturity
		
		// bellow cond removed as now we are closing this account a day before in daily closing
		// if($maturity_day) $days['days_total']--;


		$interest = $this->getAmountForInterest($on_date,false) * $this['Interest'] * $days['days_total'] / 36500;
			
		$this['LastCurrentInterestUpdatedAt'] = $on_date;

		$this['CurrentInterest'] = $this['CurrentInterest'] + round($interest,2);

	    $debitAccount = $this['branch_code'] . SP . INTEREST_PAID_ON . SP. $this['scheme_name'];
		$creditAccount = $this['branch_code'] . SP . INTEREST_PROVISION_ON . SP. $this['scheme_name'];

		$transaction = $this->add('Model_Transaction');
		$transaction->createNewTransaction(TRA_INTEREST_PROVISION_IN_FIXED_ACCOUNT, $this->ref('branch_id'), $on_date, "FD Interest Provision in ".$this['AccountNumber'], $only_transaction=null, array('reference_id'=>$this->id));
		
		$transaction->addDebitAccount($debitAccount, $interest);
		$transaction->addCreditAccount($creditAccount, $interest);
		
		$transaction->execute();

		$this->api->markProgress('Doing_Provision',0,$this['AccountNumber'].$on_date);

		$this->save();

		$transaction = null;
		unset($transaction);
	}

	function markMature($on_date=null){
		if(!$on_date) $on_date = $this->api->today;
		
		if($maturity_to_account = $this->ifMaturitytoAnotherAccount()){
			$this->withdrawl($this['CurrentBalanceCr'],$narration='Maturity Amount Transfered to '. $maturity_to_account['AccountNumber'],$accounts_to_credit=array(array($maturity_to_account['AccountNumber']=>$this['CurrentBalanceCr'])),$form=null,$on_date,$in_branch=null,$reference_id=$this->id);
		}

		if($this->isAutoRenewed()){
			throw $this->exception('Auto Renew Process');
			$this->autoRenewFD();
		}
		$id=$this->id;
		$this['MaturedStatus'] = true;
		$this->saveAndUnload();
		return $this->add('Model_Account_FixedAndMis')->load($id);
	}

	function isAutoRenewed(){
		return false;
	}

	function autoRenewFD(){

	}

	function revertProvision($on_date, $narration=null){

		if(!$narration) $narration = "Yearly/Maturity Interest posting to ". $this['AccountNumber'];

		$transaction = $this->add('Model_Transaction');
		$transaction->createNewTransaction(TRA_INTEREST_POSTING_IN_FIXED_ACCOUNT, $this->ref('branch_id'), $on_date	, $narration, $only_transaction=null, array('reference_id'=>$this->id));
		
		$debitAccount = $this['branch_code'] . SP . INTEREST_PROVISION_ON . SP. $this['scheme_name'];
		
		$transaction->addDebitAccount($debitAccount, round($this['CurrentInterest'],2));
		$transaction->addCreditAccount($this, round($this['CurrentInterest'],2));
		
		$transaction->execute();

		$this['CurrentInterest'] = 0;
		$this->save();

		$transaction = null;
		unset($transaction);

	}

	function ifMaturitytoAnotherAccount(){
		$account=$this->ref('MaturityToAccount_id');
		if($account->loaded())
			return $account;
		else
			return false;
	}

	function interstToAnotherAccountEntry($on_date,$mark_matured=false,$minus_one_day=false){
		$days = $this->api->my_date_diff($on_date,$this->app->previousDate($this['LastCurrentInterestUpdatedAt']));
		

		$days_to_count = $days['days_total'];

		if($minus_one_day) $days_to_count--;

		if(date('m',strtotime($on_date))==2){
			// Its february
			if($days_to_count >= date("t",strtotime($on_date))) $days_to_count=30;
		}
		if($days_to_count >= 30) $days_to_count=30;

		if($days_to_count==30)
			$interest = $this['Amount'] * $this['Interest'] / 1200;
		else
			$interest = $this['Amount'] * $this['Interest'] * $days_to_count / 36500;


		$interest = round($interest);

		// OLD SOFTWARE CODE FOR REFERENCE HERE : xcideveloper joomla
		// if($days['days_total'] < 30 && (date("m",strtotime(getNow("Y-m-d"))) - 1 ) !=2)
  //           $interest = $acc->RdAmount * $days['days_total'] * $sc->Interest / 36500;
  //       else
  //           $interest = round($acc->RdAmount * $sc->Interest / 1200,ROUND_TO);


		
		$this['LastCurrentInterestUpdatedAt'] = $on_date;

		$this['CurrentInterest'] = $this['CurrentInterest'] + $interest;

		// 

		$creditAccount = $this->ref('intrest_to_account_id');

		$transaction = $this->add('Model_Transaction');
		$transaction->createNewTransaction(TRA_INTEREST_POSTING_IN_MIS_ACCOUNT, $this->ref('branch_id'), $on_date, "MIS monthly Interest Posting in ".$this['AccountNumber'], $only_transaction=null, array('reference_id'=>$this->id));
		
		$transaction->addDebitAccount($this['branch_code'].SP.'Interest Paid On'.SP.$this->ref('scheme_id')->get('name'), $interest);
		$transaction->addCreditAccount($this, $interest);
		
		$transaction->execute();

		// 

		try{

			$creditAccount = $this->ref('intrest_to_account_id');

			$transaction = $this->add('Model_Transaction');
			$transaction->createNewTransaction(TRA_INTEREST_POSTING_IN_MIS_ACCOUNT, $this->ref('branch_id'), $on_date, "MIS monthly Interest Deposited from ".$this['AccountNumber']." to " . $creditAccount['AccountNumber'], $only_transaction=null, array('reference_id'=>$this->id));
			
			$transaction->addDebitAccount($this, $interest);
			$transaction->addCreditAccount($creditAccount, $interest);
			
			$transaction->execute();
		}catch(\Exception $e){
			echo $this['AccountNumber'];
			throw $e;
		}
		
		if($mark_matured) $this['MaturedStatus'] = true;
		$this->saveAndUnload();
		// throw $this->exception('interstToAnotherAccountEntry post entry to be checked');
	}

	function provisions($from_date, $to_date, $branch=null, $for_scheme=null, $for_account=null){
		if(!$branch) $branch = $this->api->currentBranch;
		
		$provision_transactions_rows = $this->add('Model_TransactionRow');
		$provision_transactions_rows->getElement('reference_id')->sortable(true);
		$provision_transactions_rows->getElement('amountDr')->sortable(true);

		$provision_transactions_join = $provision_transactions_rows->join('transactions','transaction_id');
		// $provision_transactions_join->addField('reference_id');
		$account_join = $provision_transactions_join->join('accounts','reference_id');
		$account_join->addField('AccountNumber');
		$account_join->addField('account_created','created_at')->sortable(true);
		$account_join->addField('account_braqnch_id','branch_id');

		$scheme_join = $account_join->join('schemes','scheme_id');
		$scheme_join->addField('scheme_name','name')->sortable(true);
		$scheme_join->addField('Interest');

		$transaction_type_join = $provision_transactions_join->join('transaction_types','transaction_type_id');

		$transaction_type_model = $this->add('Model_TransactionType');
		$transaction_type_model->tryLoadBy('name',TRA_INTEREST_PROVISION_IN_FIXED_ACCOUNT);

		if(!$transaction_type_model->loaded()) $transaction_type_model->save();

		$provision_transactions_rows->addCondition('transaction_type_id',$transaction_type_model->id);
		$provision_transactions_rows->addCondition('created_at','>=',$from_date);
		$provision_transactions_rows->addCondition('created_at','<',$this->api->nextDate($to_date));

		$provision_transactions_rows->addCondition('amountDr','>',0);
		// $provision_transactions_rows->_dsql()->group('transaction_id');

		return $provision_transactions_rows;
	}

	function pre_mature($on_date=null, $return_amount = false,$account_to_credit=null ,$other_charges=[], $other_bonus=[]){
		if(!$on_date) $on_date = $this->app->today;
		$info = $this->pre_mature_info($on_date);
		if(!$info['can_premature'])
			throw new \Exception("You cannot pre mature this account", 1);
			
		$amount_to_give = $this->getAmountForInterest($on_date,$calculate_remainig_days=true, $interest_rate_provided = $info['applicable_percentage']);
		if($return_amount){
			return $amount_to_give;
		}

		if(!$account_to_credit) throw new \Exception("Please provide account to credit", 1);
		
		
		$this->revertProvision($on_date,'Pre mature provision interest posting till date in '. $this['AccountNumber']);

		$transactions = $this->add('Model_TransactionRow');
		$transactions->addCondition('account_id',$this->id);
		$cr_sum = $transactions->sum('amountCr')->getOne();

		$difference = round($amount_to_give,0) - $cr_sum;

		if($difference > 0) {
			// amount to give is more and more payment need to be given now (Aur paisa dena hai)
			$transaction = $this->add('Model_Transaction');
			$transaction->createNewTransaction(TRA_INTEREST_POSTING_IN_FIXED_ACCOUNT, $this->ref('branch_id'), $on_date, 'Pre mature remaining interest posting till date in '. $this['AccountNumber'], $only_transaction=null, array('reference_id'=>$this->id));
			
			$debitAccount = $this['branch_code'] . SP . INTEREST_PAID_ON . SP. $this['scheme_name'];
			$transaction->addDebitAccount($debitAccount, $difference);
			$transaction->addCreditAccount($this, $difference);
			$transaction->execute();

		}else{
			// amount to give already given more and rverse calculation needed for payment adjustments (Pisa katna hai)
			$difference = abs($difference);
			$transaction = $this->add('Model_Transaction');
			$transaction->createNewTransaction(TRA_EXCESS_AMOUNT_REVERT, $this->ref('branch_id'), $on_date, "Excess amount reverted in ".$this['AccountNumber'], $only_transaction=null, array('reference_id'=>$this->id));
			
			$transaction->addDebitAccount($this, $difference);
			$creditAccount = $this['branch_code'] . SP . INTEREST_PAID_ON . SP. $this['scheme_name'];
			$transaction->addCreditAccount($creditAccount, $difference);
			$transaction->execute();	

		}

		

		$transactions = $this->add('Model_TransactionRow');
		$transactions->addCondition('account_id',$this->id);
		$cr_sum = $transactions->sum('amountCr')->getOne();
		$dr_sum = $transactions->sum('amountDr')->getOne();

		$final_debit_amount = $cr_sum - $dr_sum;
		
		$transaction = $this->add('Model_Transaction');
		$transaction->createNewTransaction(TRA_FD_ACCOUNT_AMOUNT_WITHDRAWL, $this->ref('branch_id'), $on_date, "FD Pre Mature Payment Given in ".$this['AccountNumber'], $only_transaction=null, array('reference_id'=>$this->id));
		
		$final_credit_amount = $final_debit_amount;

		$transaction->addDebitAccount($this, $final_debit_amount);
		if(count($other_charges)){
			$transaction->addCreditAccount($other_charges['Account'], $other_charges['Amount']);
			$final_credit_amount -= $other_charges['Amount'];
		}

		if(count($other_bonus)){
			$transaction->addDebitAccount($other_bonus['Account'], $other_bonus['Amount']);
			$final_credit_amount += $other_bonus['Amount'];
		}

		$transaction->addCreditAccount($account_to_credit, $final_credit_amount);
		$transaction->execute();

		// mark mature and deactivate
		$this['MaturedStatus']=true;
		$this['ActiveStatus']=false;
		$this->saveAndUnload();

	}

	function settleAccessOrLess($on_date,$for_date=null){
		if(!$on_date) $on_date = $this->app->today;
		if(!$for_date) $for_date = $this->app->today;
		$amount_to_give = $this->getAmountForInterest($for_date,$calculate_remainig_days=true);

		$transactions = $this->add('Model_TransactionRow');
		$transactions->addCondition('account_id',$this->id);
		$cr_sum = $transactions->sum('amountCr')->getOne();

		$difference = round($amount_to_give,0) - $cr_sum;

		if($difference > 0) {
			// amount to give is more and more payment need to be given now (Aur paisa dena hai)
			$transaction = $this->add('Model_Transaction');
			$transaction->createNewTransaction(TRA_INTEREST_POSTING_IN_FIXED_ACCOUNT, $this->ref('branch_id'), $on_date, 'Maturity remaining interest posting till date in '. $this['AccountNumber'], $only_transaction=null, array('reference_id'=>$this->id));
			
			$debitAccount = $this['branch_code'] . SP . INTEREST_PAID_ON . SP. $this['scheme_name'];
			$transaction->addDebitAccount($debitAccount, $difference);
			$transaction->addCreditAccount($this, $difference);
			$transaction->execute();

		}else{
			// amount to give already given more and rverse calculation needed for payment adjustments (Pisa katna hai)
			$difference = abs($difference);
			$transaction = $this->add('Model_Transaction');
			$transaction->createNewTransaction(TRA_EXCESS_AMOUNT_REVERT, $this->ref('branch_id'), $on_date, "Excess amount reverted in ".$this['AccountNumber'], $only_transaction=null, array('reference_id'=>$this->id));
			
			$transaction->addDebitAccount($this, $difference);
			$creditAccount = $this['branch_code'] . SP . INTEREST_PAID_ON . SP. $this['scheme_name'];
			$transaction->addCreditAccount($creditAccount, $difference);
			$transaction->execute();	

		}


	}
}