<?php
class Model_Account_Recurring extends Model_Account{
	
	public $transaction_deposit_type = TRA_RECURRING_ACCOUNT_AMOUNT_DEPOSIT;	
	public $default_transaction_deposit_narration = "Recurring Amount Deposit in {{AccountNumber}} ({{AccountHolderName}})";	
	
	public $transaction_withdraw_type = TRA_RECURRING_ACCOUNT_AMOUNT_WITHDRAWL;	
	public $default_transaction_withdraw_narration = "Amount withdrawl from {{SchemeType}} Account {{AccountNumber}}";	

	function init(){
		parent::init();

		$this->addCondition('SchemeType','Recurring');
		$this->getElement('scheme_id')->getModel()->addCondition('SchemeType','Recurring');
		$this->getElement('Amount')->caption('Premium');
		$this->getElement('account_type')->defaultValue(ACCOUNT_TYPE_RECURRING);

		$this->addExpression('maturity_date')->set(function($m,$q){
			return "DATE_ADD(DATE(".$m->dsql()->getField('created_at')."), INTERVAL +".$m->scheme_join->table_alias.".MaturityPeriod MONTH)";
		});

		$this->addExpression('locked_to_loan_till')->set(function($m,$q){
			return "DATE_ADD(DATE(".$m->dsql()->getField('created_at')."), INTERVAL +".$m->scheme_join->table_alias.".no_loan_on_deposit_till MONTH)";
		});

		$this->scheme_join->addField('percent_loan_on_deposit');
		$this->scheme_join->addField('no_loan_on_deposit_till');

		// $this->addHook('afterAccountDebited,afterAccountCredited',array($this,'closeIfPaidCompletely'));

		//$this->add('dynamic_model/Controller_AutoCreator');
	}

	function createNewAccount($member_id,$scheme_id,$branch_id, $AccountNumber,$otherValues=array(),$form=null, $on_date = null ){
		if(!$on_date) $on_date = $this->api->now;
		if(!$AccountNumber) $AccountNumber = $this->getNewAccountNumber();
		
		$scheme = $this->add('Model_Scheme')->load($scheme_id);
		if($scheme['MinLimit']){
			if($otherValues['Amount']< $scheme['MinLimit'])
				throw $this->exception('Scheme Minimum Limit is set as '.$scheme['MinLimit'], 'ValidityCheck')->setField('Amount');
		}		

		parent::createNewAccount($member_id,$scheme_id,$branch_id, $AccountNumber,$otherValues,$form, $on_date);
		$this->createPremiums();
		// return true;
		
		if($form['agent_id']){
			$this->agent()->addCRPB($this->scheme()->get('CRPB'),$this['Amount']);
		}
		
		if(isset($otherValues['initial_opening_amount']) and $otherValues['initial_opening_amount']){

			if($otherValues['debit_account']){
				$db_acc = $this->add('Model_Account',['with_balance_cr'=>true,'with_balance_dr'=>true])
					->setActualFields(['balance_cr','balance_dr','SchemeType'])
					->loadBy('AccountNumber',$form['debit_account']);
				$credit_balance = $db_acc['balance_cr'];
				$debit_balance = $db_acc['balance_dr'];	
				$compare_with = $otherValues['initial_opening_amount'];
				$error_field='initial_opening_amount';

				$is_saving_account = $db_acc['SchemeType'] == 'SavingAndCurrent';		

				if($is_saving_account){
					if($credit_balance < $compare_with) throw $this->exception('Insufficient Amount, Current Balance Cr.'. $credit_balance,'ValidityCheck')->setField($error_field);
				}
			}		

					

			$this->deposit($otherValues['initial_opening_amount'],null,$otherValues['debit_account']?[ [ $otherValues['debit_account']=>$otherValues['initial_opening_amount'] ] ]:null,null, $on_date);

			$member=$this->add('Model_Member')->load($member_id);
			$msg="Dear Member, Your account ".$AccountNumber." has been opened with amount ". $otherValues['initial_opening_amount'] . " on dated ". $this->app->today. " From:- Bhawani Credit Co-Operative Society Ltd. +91 8003597814";
			
			$mobile_no=explode(',', $member['PhoneNos']);
			if(strlen(trim($mobile_no[0])) == 10){
				$sms=$this->add('Controller_Sms');
				$sms->sendMessage($mobile_no[0],$msg);
			}
		}
	}

	function deposit($amount,$narration=null,$accounts_to_debit=null,$form=null,$on_date=null){

		$interest_till_date = $this->interestPaid($on_date);
		if(($this['CurrentBalanceCr'] + $amount - $interest_till_date) > (($this->ref('scheme_id')->get('NumberOfPremiums') * $this['Amount'])+$interest_till_date)){
			throw $this->exception(' Cannot Deposit More then '.$this->duePremiums() . ' premiums', 'ValidityCheck')
				->setField('amount')
				->addMoreInfo('Cr Amount After This Amount',($this['CurrentBalanceCr'] + $amount - $this->interestPaid($on_date)))
				->addMoreInfo('Amount Allowed',($this->ref('scheme_id')->get('NumberOfPremiums') * $this['Amount'])+$this->interestPaid($on_date))
				->addMoreInfo('Interest Till',$this->interestPaid($on_date))
				;
		}
		
		parent::deposit($amount,$narration,$accounts_to_debit,$form,$on_date);
		
		$this->payPremiumsIfAdjustedIn(0,$on_date); // Amount already added Cr Balance of Account in last line
	}

	function withdrawl($amount,$narration=null,$accounts_to_credit=null,$form=null,$on_date=null){
		if( !$this->isMatured() OR $this->isActive())
			throw $this->exception('Account is either not matured or is active, cannot withdraw', 'ValidityCheck')->setField('account');

		if(empty($accounts_to_credit) AND $amount != round(($this['CurrentBalanceCr'] - $this['CurrentBalanceDr']),0))
			throw $this->exception('CAnnot withdraw partial amount in cash : '. ($this['CurrentBalanceCr'] - $this['CurrentBalanceDr']), 'ValidityCheck')->setField('amount');

		if(!empty($accounts_to_credit) AND $amount != ($this['CurrentBalanceCr'] - $this['CurrentBalanceDr']))
			throw $this->exception('CAnnot withdraw partial amount: '. ($this['CurrentBalanceCr'] - $this['CurrentBalanceDr']), 'ValidityCheck')->setField('amount');
		
		parent::withdrawl($amount,$narration,$accounts_to_credit,$form,$on_date);
	}

	function getFirstEMIDate($return_date_string=false){
		// ??? .... $this['created_at'] with dealer_monthly_date ... relation

		$date = new MyDateTime($this['created_at']);

		return $date;

		$toAdd = 'P1M';

		if($this['dealer_id']){
			$dd=explode(",",$this['dealer_monthly_date']);
			$applicable_date = (int)date('d',strtotime($this['created_at']));
			if(count($dd)>0){
				foreach ($dd as $dealer_date) {
					if((int)$dealer_date >= (int)date('d',strtotime($this['created_at']))){
						$applicable_date = $dealer_date; 
						break;
					}
				}
				$date = new MyDateTime(date('Y-m-'.$applicable_date,strtotime($this['created_at'])));
				$date->add(new DateInterval($toAdd));
				return $date;
			}
		}
		$date->add(new DateInterval($toAdd));
		if($return_date_string)
			return $date->format('Y-m-d');
		else
			return $date;
	}

	function createPremiums(){
		if(!$this->loaded()) throw $this->exception('Account Must Be loaded to create premiums');
		
        $scheme = $this->ref('scheme_id');

		switch ($scheme['PremiumMode']) {
            case RECURRING_MODE_YEARLY:
                $toAdd = "P1Y";
                break;
            case RECURRING_MODE_HALFYEARLY:
                $toAdd = "P6M";
                break;
            case RECURRING_MODE_QUATERLY:
                $toAdd = "P3D";
                break;
            case RECURRING_MODE_MONTHLY:
                $toAdd = "P1M";
                break;
            case RECURRING_MODE_DAILY:
                $toAdd = "P1D";
                break;
        }

        $date_obj = $this->getFirstEMIDate();
        $lastPremiumPaidDate = $date_obj->format('Y-m-d');

        $rate = $scheme['Interest'];
        $premiums = $scheme['NumberOfPremiums'];
        
        $prem = $this->add('Model_Premium');
        for ($i = 1; $i <= $premiums ; $i++) {
            $prem['account_id'] = $this->id;
            $prem['Amount'] = $this['Amount'];
            $prem['DueDate'] = $lastPremiumPaidDate; // First Preiume on the day of account open
            $prem['AgentCommissionPercentage'] = $this->api->getComission($this->scheme()->get('AccountOpenningCommission'), PREMIUM_COMMISSION, $i);
            $prem['AgentCollectionChargesPercentage'] = $this->api->getComission($this->scheme()->get('CollectorCommissionRate'), PREMIUM_COMMISSION, $i);
            $date_obj->add(new DateInterval($toAdd));
            $lastPremiumPaidDate = $date_obj->format('Y-m-d');
            $prem->saveAndUnload();
        }

	}

	function payPremiumsIfAdjustedIn($amount,$on_date=null){
		if(!$on_date) $on_date = $this->api->now;

		$PremiumAmountAdjusted = $this->paidPremiums() * $this['Amount'];
		$AmountForPremiums = ($this['CurrentBalanceCr'] + $amount) - ($PremiumAmountAdjusted + $this->interestPaid($on_date));

		// way one : gave error some way so changed to 
		$premiumsSubmitedInThisAmount = number_format(floor(($AmountForPremiums / (int)$this['Amount'])));
		// this way but in case of 0.5 it is rounded to 1 so premium paid if even half or more amount submitted but not full
		$premiumsSubmitedInThisAmount = round($AmountForPremiums / (int)$this['Amount'],0);
		// Now trying this
		$premiumsSubmitedInThisAmount = (int)($AmountForPremiums / (int)$this['Amount']);

		$unpaid_premiums = $this->ref('Premium');
		// $unpaid_premiums->addCondition('Paid',false);
		$unpaid_premiums->addCondition('PaidOn',null);
		$unpaid_premiums->setOrder('id');
		$unpaid_premiums->setLimit($premiumsSubmitedInThisAmount);

		foreach ($unpaid_premiums as $unpaid_premiums_array) {
			$unpaid_premiums->payNowForRecurring($on_date); // Doing Aganet commission and Paid value also
		}

		// $this->reAdjustPaidValue($on_date);
	}

	function reAdjustPaidValue($on_date=null){
		// TODOS: Performance can be increased if this all done by joining query
		

		// more accurate method in Premiums so calling it 

		$this->ref('Premium')->reAdjustPaidValue($on_date);


		// if(!$on_date) $on_date = $this->api->today;
		// $premiums_to_affect = $this->ref('Premium');
		// $premiums_to_affect->_dsql()->where("(DueDate <='$on_date' or PaidOn is not null) and Paid = 0");
		// $premiums_to_affect->setOrder('id');

		// foreach ($premiums_to_affect as $junk) {
		// 	$paid_premiums_before_date = $this->add('Model_Premium');
		// 	$paid_premiums_before_date->addCondition('account_id',$premiums_to_affect['account_id']);
		// 	$paid_premiums_before_date->addCondition('PaidOn','<=',date('Y-m-t',strtotime($on_date)));
		// 	$paid_premiums_before_date->addCondition('id','<=',$premiums_to_affect->id);

		// 	$premiums_to_affect['Paid'] = $paid_premiums_before_date->count()->getOne();
		// 	$premiums_to_affect->saveAs('Model_Premium');
		// }

	}

	function duePremiums($as_on_date=null){
		if(!$this->loaded()) throw $this->exception('Account Must be loaded to get due Premiums');
		
		$prem = $this->ref('Premium');
		$prem->addCondition('PaidOn',null);

		if($as_on_date) $prem->addCondition('DueDate','<=',$as_on_date);
		
		return $prem->count()->getOne();

	}

	function paidPremiums($as_on_date=null){
		if(!$this->loaded()) throw $this->exception('Account Must be loaded to get paid Premiums');
		
		$prem = $this->ref('Premium');
		$prem->addCondition('PaidOn','<>',null);

		if($as_on_date) $prem->addCondition('DueDate','<=',$as_on_date);
		
		return $prem->count()->getOne();
	}

	function maxPaidPremium($as_on_date=null){
		$prem = $this->ref('Premiums');
		$prem->addCondition('DueDate','<=',$as_on_date);
		$prem->addCondition('Paid','>',0);

		return $prem->_dsql()->del('fields')->field($this->dsql()->expr('max(Paid)'))->getOne();
	}

	function interestPaid($as_on_date=null){
		if(!$as_on_date) $as_on_date = $this->api->now;

		$transactions = $this->add('Model_Transaction');
		$rows_join = $transactions->join('transaction_row.transaction_id');
		$rows_join->hasOne('Account','account_id');
		$rows_join->addField('amountCr');

		$transaction_type_join = $transactions->join('transaction_types','transaction_type_id');
		$transaction_type_join->addField('transaction_type_name','name');

		$transactions->addCondition('transaction_type_name',TRA_INTEREST_POSTING_IN_RECURRING);
		$transactions->addCondition('account_id',$this->id);
		$transactions->addCondition('created_at','<',$as_on_date);

		return $transactions->sum($transactions->dsql()->expr('IFNULL([0],0)',array($transactions->getElement('amountCr'))))->getOne();

	}

	function payInterest($on_date=null){
		if(!$on_date) $on_date = $this->api->now;

		$fy = $this->api->getFinancialYear($on_date);

		$non_interest_paid_premiums_till_now = $this->add('Model_Premium');
		$non_interest_paid_premiums_till_now->addCondition('account_id',$this->id);
		$non_interest_paid_premiums_till_now->addCondition('Paid','>=',1);
		$non_interest_paid_premiums_till_now->addExpression('EffectivePaidDate')->set('IFNULL(PaidOn,DueDate)');

		$non_interest_paid_premiums_till_now->addCondition('EffectivePaidDate','>=',$fy['start_date']);
		$non_interest_paid_premiums_till_now->addCondition('EffectivePaidDate','<',$this->api->nextDate($fy['end_date']));
		$non_interest_paid_premiums_till_now->addCondition('DueDate','>=',$fy['start_date']);
		$non_interest_paid_premiums_till_now->addCondition('DueDate','<',$this->api->nextDate($fy['end_date']));

		$interest_paid = $this->interestPaid($on_date);

		// echo "interest paid $interest_paid <br/>";

		$product = $non_interest_paid_premiums_till_now->_dsql()->del('fields')->field('sum((Paid*Amount)+'.($interest_paid?:0).')')->getOne();

		// echo "product $product <br/>";
		$premium_mode = $this->ref('scheme_id')->get('PremiumMode');
		$x = ($premium_mode=='Y')?1:12;
		$interest = ($product * $this->ref('scheme_id')->get('Interest'))/($x*100);
		// echo "interest $interest <br/>";


		// Interest ... TDS Deduct ????? :: asumed as not to give as too long time has done ... 

		$transaction = $this->add('Model_Transaction');
		$transaction->createNewTransaction(TRA_INTEREST_POSTING_IN_RECURRING, $this->ref('branch_id'), $on_date, 'Interest posting in Recurring Account', null, array('reference_id'=>$this->id));
		
		$transaction->addDebitAccount($this['branch_code'] . SP . INTEREST_PAID_ON . SP. $this['scheme_name'], $interest);
		$transaction->addCreditAccount($this, $interest);
		
		$transaction->execute();

		
	}

	function markMatured($on_date=null){
		if(!$this->loaded()) throw new \Exception("Account must be loaded to mark mature", 1);
		if(!$on_date) $on_date = $this->api->now;

		$this->payInterest();

		$this->revertAccessInterest($on_date);
	
		
		$this['MaturedStatus'] = true;
		$this->saveAs('Account_Recurring');
	}

	function revertAccessInterest($on_date=null){
		if(!$on_date) $on_date = $this->api->now;
		// if not product completed .. use non_product_percentage
		if($this->premiums()->count()->getOne() == $this->premiums()->_dsql()->del('fields')->field($this->dsql()->expr('max(Paid)'))->getOne()){
			$percentages=null ; // actual as in scheme
		}else{
			$percentages=$this->ref('scheme_id')->get('mature_interests_for_uncomplete_product')?:'4';
		}

		$this->pre_mature($on_date,null,null,null,null,$percentages,true);
	}

	function premiums(){
		return $this->add('Model_Premium')->addCondition('account_id',$this->id);
	}

	function pre_mature_info($on_date = null , $is_maturity =false ){
		if(!$on_date) $on_date = $this->app->today;
		$info = [];
		$this_scheme = $this->ref('scheme_id');
		$per_string = $info['pre_maturity_percentages_string'] = $this_scheme['pre_mature_interests'];
		$percentages = $info['pre_maturity_percentages'] = explode(",", trim($info['pre_maturity_percentages_string']));
		
		$non_product_interst_percent = $percentages[0];
		unset($percentages[0]);
		
		$info['days_months_total'] = ($this->app->my_date_diff(date('Y-m-d',strtotime($this['created_at'])),$on_date)['months_total']);

		$info['applicable_percentage'] = $this_scheme['Interest'];

		if($is_maturity) return $info;

		$info['can_premature']=false;
		$info['premiums_paid']=$this->paidPremiums();
		foreach ($percentages as $day_percent_pair) {
			$array = explode(":", $day_percent_pair);
			if(count($array)!=2) break;
			if($info['days_months_total'] >= (float)$array[0]){
				if($this->paidPremiums() >= $info['days_months_total'])
					$info['applicable_percentage'] = $array[1];
				else
					$info['applicable_percentage'] = $non_product_interst_percent;
				$info['can_premature']=true;
				break;
			}
		}

		return $info;
	}

	function pre_mature($on_date=null,$return_amount = false,$account_to_credit=null ,$other_charges=[], $other_bonus=[],$percentages=null,$is_maturity=false){
		
		if(!$on_date) $on_date = $this->app->now;
		$info = $this->pre_mature_info($on_date,$is_maturity);
		
		if(!$is_maturity && !$info['can_premature'])
			throw new \Exception("You cannot pre mature this account", 1);
		// throw new \Exception("YES", 1);

		$given_interest = $this->interestPaid($on_date);

		$new_applicable_percentage = $percentages?:$info['applicable_percentage'];

		// echo "calculating pre_mature as per  $new_applicable_percentage % ". $info['applicable_percentage'] ."<br/>";

		$months_to_check = $info['days_months_total'];

		$years_completed = (int) ($months_to_check / 12) ;
		$uncompleted_months = $months_to_check - ( 12 * $years_completed);
		

		$product = 0;
		$interest = 0;
		// $interest_total=0;

		$loop = $years_completed + ($uncompleted_months>0?1:0);
		
		$premium_mode = $this->ref('scheme_id')->get('PremiumMode');
		$x = ($premium_mode=='Y')?1:12;

		for($i=1;$i<=$loop;$i++){

			$non_interest_paid_premiums_till_now = $this->add('Model_Premium');
			$non_interest_paid_premiums_till_now->addCondition('account_id',$this->id);
			$non_interest_paid_premiums_till_now->addCondition('Paid','>=',1);

			// $limit_sql = clone $non_interest_paid_premiums_till_now->_dsql()->limit(12,(12*($i-1))); // now converting from monthly to yearly also
			$limit_sql = clone $non_interest_paid_premiums_till_now->_dsql()->limit($x,($x*($i-1)));

			$product += $non_interest_paid_premiums_till_now->dsql()
									// ->expr('select sum((Paid*Amount)+'.$interest.') from (select Paid, Amount from premiums WHERE account_id='.$this->id.' and Paid>=1 limit '.(12*($i-1)).', 12) as temp') // monthly to yearly also
									->expr('select sum((Paid*Amount)+'.$interest.') from (select Paid, Amount from premiums WHERE account_id='.$this->id.' and Paid>=1 limit '.($x*($i-1)).', '.$x.') as temp')
									// ->debug()
									->getOne();
			$interest = ($product * $new_applicable_percentage)/($x*100);
			// echo $product ." - " ;
			// echo $interest ." - <br/>" ;
			// $interest_total += $interest;
		}

		$amount_to_give  = round($this->paidPremiums() * $this['Amount'] + $interest);

		// echo "amount to give $amount_to_give <br/>";

		if($return_amount){
			return $amount_to_give;
		}

		$transactions = $this->add('Model_TransactionRow');
		$transactions->addCondition('account_id',$this->id);
		$cr_sum = $transactions->sum('amountCr')->getOne();

		$difference = $amount_to_give - $cr_sum;
		$final_debit_amount = $difference;

		// echo "CR sum $cr_sum for account_id ".$this->id."<br/>";
		// echo "Amount to debit $final_debit_amount <br/>";

		if($difference > 0) {
			// amount to give is more and more payment need to be given now (Aur paisa dena hai)
			$transaction = $this->add('Model_Transaction');
			$transaction->createNewTransaction(TRA_INTEREST_POSTING_IN_RECURRING, $this->ref('branch_id'), $on_date, 'Pre mature remaining interest posting till date in '. $this['AccountNumber'], $only_transaction=null, array('reference_id'=>$this->id));
			
			$debitAccount = $this['branch_code'] . SP . INTEREST_PAID_ON . SP. $this['scheme_name'];
			$transaction->addDebitAccount($debitAccount, $difference);
			$transaction->addCreditAccount($this['AccountNumber'], $difference);
			$transaction->execute();

		}else{
			// amount to give already given more and rverse calculation needed for payment adjustments (Pisa katna hai)
			$difference = abs($difference);
			$transaction = $this->add('Model_Transaction');
			$transaction->createNewTransaction(TRA_EXCESS_AMOUNT_REVERT, $this->ref('branch_id'), $on_date, "Excess amount reverted in ".$this['AccountNumber'], $only_transaction=null, array('reference_id'=>$this->id));
			
			$transaction->addDebitAccount($this['AccountNumber'], $difference);
			$creditAccount = $this['branch_code'] . SP . INTEREST_PAID_ON . SP. $this['scheme_name'];
			$transaction->addCreditAccount($creditAccount, $difference);
			$transaction->execute();	

		}

		// die('account_to_credit ' . $account_to_credit);

		if($account_to_credit){
			// only for premature.. but not in maturity case if this same function is reused
			$transaction = $this->add('Model_Transaction');
			$transaction->createNewTransaction($this->transaction_withdraw_type, $this->ref('branch_id'), $on_date, "RD Pre Mature Payment Given in ".$this['AccountNumber'], $only_transaction=null, array('reference_id'=>$this->id));
			
			$final_credit_amount = $amount_to_give;

			$transaction->addDebitAccount($this['AccountNumber'], $amount_to_give);
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
		}		

		// mark mature and deactivate
		$this['MaturedStatus']=true;
		$this['ActiveStatus']=false;
		$o = $this->saveAs('Account_Recurring');
		if(!$this->loaded()) throw new \Exception("Oops, account is unloaded somewhere 2", 1);


	}


}