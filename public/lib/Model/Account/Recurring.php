<?php
class Model_Account_Recurring extends Model_Account{
	
	public $transaction_deposit_type = TRA_RECURRING_ACCOUNT_AMOUNT_DEPOSIT;	
	public $default_transaction_deposit_narration = "Recurring Amount Deposit in {{AccountNumber}}";	

	function init(){
		parent::init();

		$this->addCondition('SchemeType','Recurring');
		$this->getElement('scheme_id')->getModel()->addCondition('SchemeType','Recurring');
		$this->getElement('Amount')->caption('RECURRING amount (premium)');

		$this->addExpression('maturity_date')->set(function($m,$q){
			return "DATE_ADD(DATE(".$m->dsql()->getField('created_at')."), INTERVAL +".$m->scheme_join->table_alias.".MaturityPeriod MONTH)";
		});

		//$this->add('dynamic_model/Controller_AutoCreator');
	}

	function createNewAccount($member_id,$scheme_id,$branch_id, $AccountNumber,$otherValues=array(),$form=null, $on_date = null ){
		if(!$on_date) $on_date = $this->api->now;
		
		parent::createNewAccount($member_id,$scheme_id,$branch_id, $AccountNumber,$otherValues,$form, $on_date);
		
		$this->createPremiums();
		
		if(isset($form['initial_opening_amount']) and $form['initial_opening_amount'])
			$this->deposit($form['initial_opening_amount'],null,null,null, $on_date);
	}

	function deposit($amount,$narration=null,$accounts_to_debit=null,$form=null,$on_date=null){

		if(($this['CurrentBalanceCr'] + $amount - $this->interestPaid($on_date)) > ($this->ref('scheme_id')->get('NumberOfPremiums') * $this['Amount'])){
			throw $this->exception(' CAnnot Deposit More then '.$this->duePremiums() . ' premiums', 'ValidityCheck')->setField('amount');
		}
		
		parent::deposit($amount,$narration,$accounts_to_debit,$form,$on_date);
		
		$this->payPremiumsIfAdjustedIn($amount,$on_date);
	}

	function withdrawl($amount,$narration=null,$accounts_to_credit=null,$form=null,$on_date=null){
		if( ! $this->isMatured() OR $this->isActive())
			throw $this->exception('Account is wither not matured or is active, cannot withdraw', 'ValidityCheck')->setField('account');

		if($amount != ($this['CurrentBalanceCr'] - $this['CurrentBalanceDr']))
			throw $this->exception('CAnnot withdraw partial amount : '. ($this['CurrentBalanceCr'] - $this['CurrentBalanceDr']), 'ValidityCheck')->setField('amount');

		parent::withdrawl($amount,$narration,$accounts_to_credit,$form,$on_date);
	}

	function createPremiums(){
		if(!$this->loaded()) throw $this->exception('Account Must Be loaded to create premiums');
		
		$scheme = $this->ref('scheme_id');

		switch ($scheme['PremiumMode']) {
            case RECURRING_MODE_YEARLY:
                $toAdd = " +1 year";
                break;
            case RECURRING_MODE_HALFYEARLY:
                $toAdd = " +6 month";
                break;
            case RECURRING_MODE_QUATERLY:
                $toAdd = " +3 month";
                break;
            case RECURRING_MODE_MONTHLY:
                $toAdd = " +1 month";
                break;
            case RECURRING_MODE_DAILY:
                $toAdd = " +1 day";
                break;
        }

        $lastPremiumPaidDate = $this['created_at'];
        $rate = $scheme['Interest'];
        $premiums = $scheme['NumberOfPremiums'];
        
        $prem = $this->add('Model_Premium');
        for ($i = 1; $i <= $premiums ; $i++) {
            $prem['account_id'] = $this->id;
            $prem['Amount'] = $this['Amount'];
            $prem['DueDate'] = $lastPremiumPaidDate; // First Preiume on the day of account open
            $lastPremiumPaidDate = date("Y-m-d", strtotime(date("Y-m-d", strtotime($lastPremiumPaidDate)) . $toAdd));
            $prem->saveAndUnload();
        }

	}

	function payPremiumsIfAdjustedIn($amount,$on_date=null){
		if(!$on_date) $on_date = $this->api->now;

		$PremiumAmountAdjusted = $this->paidPremiums() * $this['Amount'];
		$AmountForPremiums = $this['CurrentBalanceCr'] + $amount - $PremiumAmountAdjusted - $this->interestPaid($on_date);

		$premiumsSubmitedInThisAmount = (int) ($AmountForPremiums / $this['Amount']);

		$unpaid_premiums = $this->ref('Premiums');
		$unpaid_premiums->addCondition('Paid',false);
		$unpaid_premiums->setOrder('id');
		$unpaid_premiums->setLimit($premiumsSubmitedInThisAmount);

		foreach ($unpaid_premiums as $unpaid_premiums_array) {
			$unpaid_premiums->payNowForRecurring($on_date); // Doing Aganet commission and Paid value also
		}

	}

	function duePremiums($as_on_date=null){
		if(!$this->loaded()) throw $this->exception('Account Must be loaded to get due Premiums');
		
		$prem = $this->ref('Premiums');
		$prem->addCondition('Paid',0);

		if($as_on_date) $prem->addCondition('DueDate','<=',$as_on_date);
		
		return $prem->count()->getOne();

	}

	function paidPremiums($as_on_date=null){
		if(!$this->loaded()) throw $this->exception('Account Must be loaded to get paid Premiums');
		
		$prem = $this->ref('Premiums');
		$prem->addCondition('Paid','<>',0);

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

		$transactions = $this->add('Model_Transactions');
		$rows_join = $transactions->join('transaction_row.transaction_id');
		$rows_join->hasOne('Account','account_id');

		$transactions->addCodnition('trsnaction',TRA_INTEREST_POSTING_IN_RECURRING);
		$transactions->addCodnition('account_id',$this->id);
		$transactions->addCodnition('created_at','<',$as_on_date);

		return $transactions->sum('amountCr')->getOne();

	}

	function payInterest($on_date=null){
		if(!$on_date) $on_date = $this->api->now;

		$fy = $this->api->getFinancialYear($on_date);

		$non_interest_paid_premiums_till_now = $this->ref('Premiums');
		$non_interest_paid_premiums_till_now->addCodnition('Paid',true);
		$non_interest_paid_premiums_till_now->addCodnition('PaidOn','>=',$fy['start_date']);
		$non_interest_paid_premiums_till_now->addCodnition('PaidOn','<',$this->api->nextDate($fy['end_date']));

		$product = $non_interest_paid_premiums_till_now->_dsql()->del('fields')->field('sum(Paid*Amount)');

		$interest = ($product * $this->ref('scheme_id')->get('Interest'))/1200;

		$transaction = $this->add('Model_Transaction');
		$transaction->createNewTransaction(TRA_INTEREST_POSTING_IN_RECURRING, $this->ref('branch_id'), $on_date, 'Interest posting in Recurring Account', null, array('reference_account_id'=>$this->id));
		
		$transaction->addDebitAccount($this['branch_code'] . SP . INTEREST_PAID_ON . SP. $this['scheme_name'], $interest);
		$transaction->addCreditAccount($this, $interest);
		
		$transaction->execute();

		
	}

	function markMatured($on_date=null){
		if(!$on_date) $on_date = $this->api->now;
		
		$this->payInterest();

		$this['MaturedStatus'] = true;
		$this->save();
	}

}