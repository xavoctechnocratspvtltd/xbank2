<?php
class Model_Scheme_DDS extends Model_Scheme {
	
	public $loanType = true;
	public $schemeType = ACCOUNT_TYPE_DDS;
	public $schemeGroup = ACCOUNT_TYPE_DDS;

	function init(){
		parent::init();

		$this->getElement('ProcessingFeesinPercent')->destroy();
		$this->getElement('InterestMode')->destroy();
		$this->getElement('InterestRateMode')->destroy();
		$this->getElement('LoanType')->destroy();
		$this->getElement('Commission')->destroy();
		$this->getElement('PostingMode')->destroy();
		$this->getElement('PremiumMode')->destroy();
		$this->getElement('ProcessingFees')->destroy();
		$this->getElement('CreateDefaultAccount')->destroy();
		$this->getElement('InterestToAnotherAccountPercent')->destroy();
		$this->getElement('AgentSponsorCommission')->destroy();
		$this->getElement('CollectorCommissionRate')->destroy();
		$this->getElement('published')->destroy();
		$this->getElement('ReducingOrFlatRate')->destroy();
		$this->getElement('NumberOfPremiums')->destroy();
		$this->getElement('InterestToAnotherAccount')->destroy();
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
		if(!$on_date) $on_date = $this->api->today;

		$matured_dds_accounts = $this->add('Model_Account_DDS');
		$matured_dds_accounts->scheme_join->addField('MaturityPeriod');
		$matured_dds_accounts->addCondition('ActiveStatus',true);
		$matured_dds_accounts->addCondition('MaturedStatus',false);
		$matured_dds_accounts->addCondition('branch_id',$branch->id);
		$matured_dds_accounts->addCondition("maturity_date",$on_date);
		$matured_dds_accounts->setLimit(1);

		foreach ($matured_dds_accounts as $acc) {
			$matured_dds_accounts->markMatured($on_date);
		}

	}

	function monthly($branch, $on_date){
		if(!$branch) $branch = $this->api->current_branch;
		if(!$on_date) $on_date = $this->api->today;
		throw $this->exception('To be done DDS monthly closing');		
	}

}