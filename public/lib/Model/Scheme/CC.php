<?php
class Model_Scheme_CC extends Model_Scheme {

	public $loanType = true;
	public $schemeType = ACCOUNT_TYPE_CC;
	public $schemeGroup = ACCOUNT_TYPE_CC;

	function init(){
		parent::init();

		$this->getElement('ProcessingFeesinPercent')->caption('Check if Processing Fee in %');
		$this->getElement('balance_sheet_id')->caption('Head');
		$this->getElement('InterestMode')->destroy();
		$this->getElement('InterestRateMode')->destroy();
		$this->getElement('LoanType')->destroy();
		// $this->getElement('AccountOpenningCommission')->destroy();
		$this->getElement('Commission')->destroy();
		$this->getElement('PostingMode')->destroy();
		$this->getElement('PremiumMode')->destroy();
		$this->getElement('CreateDefaultAccount')->destroy();
		$this->getElement('MaturityPeriod')->destroy();
		$this->getElement('InterestToAnotherAccountPercent')->destroy();
		$this->getElement('isDepriciable')->destroy();
		$this->getElement('DepriciationPercentBeforeSep')->destroy();
		$this->getElement('DepriciationPercentAfterSep')->destroy();
		$this->getElement('AgentSponsorCommission')->destroy();
		$this->getElement('CollectorCommissionRate')->destroy();
		$this->getElement('published')->destroy();
		$this->getElement('InterestToAnotherAccount')->destroy();
		$this->getElement('NumberOfPremiums')->destroy();

		$this->addCondition('SchemeType',$this->schemeType);

		//$this->add('dynamic_model/Controller_AutoCreator');
	}

	function getDefaultAccounts(){
		return array(
			"Indirect Income"=>"Interest Received On",
			"Indirect Income"=>"Processing Fee Received On",
			"Indirect Income"=>"Renewal Charges Received On",
			"Indirect Income"=>"CC Overdue Charges Received On",
			);
	}

	function monthly($branch=null,$on_date=null){

		if(!$on_date) $on_date = $this->api->today;
		if(!$branch) $branch=$this->api->current_branch;

		$this->resetCurrentInterest();

		$cc_accounts = $this->add('Model_Account_CC');
		$cc_accounts->addCondition('ActiveStatus',true);
		$cc_accounts->addCondition('branch_id',$branch->id);
		$cc_accounts->addCondition('created_at','<',$on_date);
		$cc_accounts->scheme_join->addField('Interest');

		foreach ($cc_accounts as $accounts_array) {
			$cc_accounts->applyMonthlyInterest($on_date);
		}
	}

	function resetCurrentInterest(){
		$accounts = $this->add('Model_Account');
		$accounts->addCondition('SchemeType',ACCOUNT_TYPE_CC);
		$accounts->addCondition('branch_id',$this->api->current_branch->id);

		$accounts->_dsql()
			// May need to uncomment this for first closing through this application version
			// ->set('LastCurrentInterestUpdatedAt',$this->api->db->dsql()->expr("DATE_ADD('" . $this->api->today . "',INTERVAL -1 MONTH)"))
			->set('CurrentInterest',0)
			;
		$accounts->_dsql()->update();
	}
}