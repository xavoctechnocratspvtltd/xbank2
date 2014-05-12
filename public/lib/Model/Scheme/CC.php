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
			"Indirect Income"=>"Processing Fee Received On"
			);
	}

	function monthly(){
		$this->resetCurrentInterest();

	}

	function resetCurrentInterest(){
		$accounts = $this->add('Model_Account');
		$transaction_row_join = $accounts->join('transaction_row.account_id');
		$transaction_join = $transaction_row_join->join('transactions','transaction_id');
		$transaction_join->addField('transaction_date','created_at');

		$accounts->addCondition('transaction_date','between',$this->dsql()->expr("DATE_ADD('" . $this->api->today . "',INTERVAL -1 MONTH) and '".$this->api->today."'"));
		$accounts->addCondition('SchemeType',ACCOUNT_TYPE_CC);

		$accounts->_dsql()
			->set('LastCurrentInterestUpdatedAt',$this->api->db->dsql()->expr("DATE_ADD('" . $this->api->today . "',INTERVAL -1 MONTH)"))
			->set('CurrentInterest',0)
			;
		$accounts->_dsql()->update();
	}
}