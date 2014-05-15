<?php
class Model_Scheme_Recurring extends Model_Scheme {
	
	public $loanType = true;
	public $schemeType = 'Recurring';
	public $schemeGroup = 'Recurring';

	function init(){
		parent::init();

		$this->getElement('ProcessingFeesinPercent')->destroy();
		$this->getElement('InterestMode')->destroy();
		$this->getElement('InterestRateMode')->destroy();
		$this->getElement('LoanType')->destroy();
		$this->getElement('Commission')->destroy();
		$this->getElement('PostingMode')->destroy();
		$this->getElement('ProcessingFees')->destroy();
		$this->getElement('CreateDefaultAccount')->destroy();
		$this->getElement('InterestToAnotherAccountPercent')->destroy();
		$this->getElement('AgentSponsorCommission')->destroy();
		// $this->getElement('CollectorCommissionRate')->destroy();
		$this->getElement('published')->destroy();
		$this->getElement('ReducingOrFlatRate')->destroy();
		$this->getElement('InterestToAnotherAccount')->destroy();
		// $this->getElement('MaturityPeriod')->destroy();
		$this->getElement('isDepriciable')->destroy();
		$this->getElement('DepriciationPercentBeforeSep')->destroy();
		$this->getElement('DepriciationPercentAfterSep')->destroy();
		
		$this->getElement('balance_sheet_id')->caption('Head');
		$this->getElement('MaturityPeriod')->caption('Period of Maturity for FD, MIS, RD, DDS(in months)');

		$this->addCondition('SchemeType',$this->schemeType);

		//$this->add('dynamic_model/Controller_AutoCreator');
	}

	function getDefaultAccounts(){
		return array(
			"Indirect Expenses"=>"Commission Paid On",
			"Indirect Expenses"=>"Interest Paid On",
			// "Indirect Expenses"=>"Collection Charges Paid On",
			"Provision"=>"Commission Payable On",
			"Provision"=>"Interest Payable On",
			// "Provision"=>"Collection Payable On"
			);
	}

	function daily($branch=null,$on_date=null){
		if(!$branch) $branch = $this->api->current_branch;
		if(!$on_date) $on_date = $this->api->now;

		$all_todays_matured_Accounts = $this->add('Model_Active_Account_Recurring');
		$all_todays_matured_Accounts->addCondition('branch_id',$branch->id);
		$all_todays_matured_Accounts->addCondition('maturity_date',$on_date);
		$all_todays_matured_Accounts->addCondition('MaturityStatus',false);

		foreach ($all_todays_matured_Accounts as $acc_array) {
			$all_todays_matured_Accounts->markMatured($on_date);
		}


	}
}