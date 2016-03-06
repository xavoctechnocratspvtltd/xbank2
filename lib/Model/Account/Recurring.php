<?php
class Model_Account_Recurring extends Model_Account{
	
	public $transaction_deposit_type = TRA_RECURRING_ACCOUNT_AMOUNT_DEPOSIT;	
	public $default_transaction_deposit_narration = "Recurring Amount Deposit in {{AccountNumber}}";	
	
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

		// $this->addHook('afterAccountDebited,afterAccountCredited',array($this,'closeIfPaidCompletely'));

		//$this->add('dynamic_model/Controller_AutoCreator');
	}

	function createNewAccount($member_id,$scheme_id,$branch_id, $AccountNumber,$otherValues=array(),$form=null, $on_date = null ){
		if(!$on_date) $on_date = $this->api->now;
		if(!$AccountNumber) $AccountNumber = $this->getNewAccountNumber();
		
		parent::createNewAccount($member_id,$scheme_id,$branch_id, $AccountNumber,$otherValues,$form, $on_date);
		$this->createPremiums();
		// return true;
		
		if($form['agent_id']){
			$this->agent()->addCRPB($this->scheme()->get('CRPB'),$this['Amount']);
		}
		
		if(isset($otherValues['initial_opening_amount']) and $otherValues['initial_opening_amount'])
			$this->deposit($otherValues['initial_opening_amount'],null,null,null, $on_date);
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
			throw $this->exception('Account is wither not matured or is active, cannot withdraw', 'ValidityCheck')->setField('account');

		if($amount != round(($this['CurrentBalanceCr'] - $this['CurrentBalanceDr']),0))
			throw $this->exception('CAnnot withdraw partial amount : '. ($this['CurrentBalanceCr'] - $this['CurrentBalanceDr']), 'ValidityCheck')->setField('amount');

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

		$premiumsSubmitedInThisAmount = number_format(floor(($AmountForPremiums / $this['Amount'])));

		// throw new Exception($premiumsSubmitedInThisAmount, 1);
		

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
		
		if(!$on_date) $on_date = $this->api->today;
		$premiums_to_affect = $this->ref('Premium');
		$premiums_to_affect->_dsql()->where("(DueDate <='$on_date' or PaidOn is not null) and Paid = 0");
		$premiums_to_affect->setOrder('id');

		foreach ($premiums_to_affect as $junk) {
			$paid_premiums_before_date = $this->add('Model_Premium');
			$paid_premiums_before_date->addCondition('account_id',$premiums_to_affect['account_id']);
			$paid_premiums_before_date->addCondition('PaidOn','<=',date('Y-m-t',strtotime($on_date)));
			$paid_premiums_before_date->addCondition('id','<=',$premiums_to_affect->id);

			$premiums_to_affect['Paid'] = $paid_premiums_before_date->count()->getOne();
			$premiums_to_affect->saveAs('Model_Premium');
		}

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

		return $transactions->sum('amountCr')->getOne();

	}

	function payInterest($on_date=null){
		if(!$on_date) $on_date = $this->api->now;

		$fy = $this->api->getFinancialYear($on_date);

		$non_interest_paid_premiums_till_now = $this->ref('Premium');
		$non_interest_paid_premiums_till_now->addCondition('Paid',true);
		$non_interest_paid_premiums_till_now->addCondition('PaidOn','>=',$fy['start_date']);
		$non_interest_paid_premiums_till_now->addCondition('PaidOn','<',$this->api->nextDate($fy['end_date']));

		$interest_paid = $this->interestPaid($on_date);

		$product = $non_interest_paid_premiums_till_now->_dsql()->del('fields')->field('sum((Paid*Amount)+'.$interest_paid.')')->getOne();

		$interest = ($product * $this->ref('scheme_id')->get('Interest'))/1200;

		// Interest ... TDS Deduct ????? :: asumed as not to give as too long time has done ... 

		$transaction = $this->add('Model_Transaction');
		$transaction->createNewTransaction(TRA_INTEREST_POSTING_IN_RECURRING, $this->ref('branch_id'), $on_date, 'Interest posting in Recurring Account', null, array('reference_id'=>$this->id));
		
		$transaction->addDebitAccount($this['branch_code'] . SP . INTEREST_PAID_ON . SP. $this['scheme_name'], $interest);
		$transaction->addCreditAccount($this, $interest);
		
		$transaction->execute();

		
	}

	function markMatured($on_date=null){
		if(!$on_date) $on_date = $this->api->now;
		
		$this->payInterest();

		$this->revertAccessInterest();

		$this['MaturedStatus'] = true;
		$this->save();
	}

	function revertAccessInterest(){

	}

	function premiums(){
		return $this->add('Model_Premium')->addCondition('account_id',$this->id);
	}


}