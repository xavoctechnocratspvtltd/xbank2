<?php
class Model_Scheme_FixedAndMis extends Model_Scheme {
	
	public $loanType = true;
	public $schemeType = ACCOUNT_TYPE_FIXED;
	public $schemeGroup = ACCOUNT_TYPE_FIXED;

	function init(){
		parent::init();
		
		$this->getElement('type')->enum(array('FD','MIS'));

		$this->getElement('ProcessingFeesinPercent')->destroy();
		$this->getElement('InterestMode')->destroy();
		$this->getElement('InterestRateMode')->destroy();
		$this->getElement('Commission')->destroy();
		$this->getElement('PostingMode')->destroy();
		$this->getElement('PremiumMode')->destroy();
		$this->getElement('ProcessingFees')->destroy();
		$this->getElement('CreateDefaultAccount')->destroy();
		$this->getElement('InterestToAnotherAccountPercent')->destroy();
		$this->getElement('isDepriciable')->destroy();
		$this->getElement('DepriciationPercentBeforeSep')->destroy();
		$this->getElement('DepriciationPercentAfterSep')->destroy();
		$this->getElement('AgentSponsorCommission')->destroy();
		$this->getElement('CollectorCommissionRate')->destroy();
		$this->getElement('published')->destroy();
		$this->getElement('ReducingOrFlatRate')->destroy();
		$this->getElement('NumberOfPremiums')->destroy();
		
		$this->getElement('balance_sheet_id')->caption('Head');
		$this->getElement('MaturityPeriod')->caption('Period of Maturity for FD (in Days )');
		$this->getElement('AccountOpenningCommission')->caption('Account Commissions(in %)');
		$this->getElement('InterestToAnotherAccount')->caption('Interest To Account (check if interest to be posted to other account)');

		$this->addCondition('SchemeType',$this->schemeType);

		//$this->add('dynamic_model/Controller_AutoCreator');
	}

	function getDefaultAccounts(){
		return array(
			array('under_scheme'=>"Indirect Expenses",'intermediate_text'=>"Commission Paid On",'Group'=>'Commission Paid On FD and MIS','PAndLGroup'=>'Commission Paid On Deposit'),
			array('under_scheme'=>"Indirect Expenses",'intermediate_text'=>"Interest Paid On",'Group'=>'Interest Paid On FD and MIS','PAndLGroup'=>'Interest Paid On Deposit'),
			array('under_scheme'=>"Provision",'intermediate_text'=>"Interest Provision On",'Group'=>'Interest Provision On FD and MIS','PAndLGroup'=>'Interest Payable On Deposit'),
			array('under_scheme'=>"Provision",'intermediate_text'=>"Commission Payable On",'Group'=>'Commission Payable On FD and MIS','PAndLGroup'=>'Commission Payable Paid On Deposit'),
			);
	}
}