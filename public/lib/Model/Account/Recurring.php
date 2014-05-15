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
		
	}

	function deposit($amount,$narration=null,$accounts_to_debit=array(),$form=null,$on_date=null){
		throw new Exception("Check For Premiums and commissions etc first", 1);

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
}