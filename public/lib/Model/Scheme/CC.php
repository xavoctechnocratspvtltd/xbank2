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
		$this->getElement('type')->destroy();
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
			array('under_scheme'=>"Indirect Income",'intermediate_text'=>"Interest Received On",'Group'=>'Interest Received on CC','PAndLGroup'=>'Interest Received on CC'),
			array('under_scheme'=>"Indirect Income",'intermediate_text'=>"Processing Fee Received On",'Group'=>'Processing Fee Received On CC','PAndLGroup'=>'Processing Fee Received On CC'),
			array('under_scheme'=>"Indirect Income",'intermediate_text'=>"Renewal Charges Received On",'Group'=>'Renewal Charges Received On CC','PAndLGroup'=>'Renewal Charges Received On CC'),
			array('under_scheme'=>"Indirect Income",'intermediate_text'=>"CC Overdue Charges Received On",'Group'=>'CC Overdue Charges Received On CC','PAndLGroup'=>'CC Overdue Charges Received On CC'),
			);
	}

	function daily($branch=null,$on_date=null,$test_account=null){

	}

	function monthly($branch=null,$on_date=null,$test_account=null){

		if(!$on_date) $on_date = $this->api->today;
		if(!$branch) $branch=$this->api->current_branch;


		$cc_accounts = $this->add('Model_Active_Account_CC');
		$cc_accounts->addCondition('branch_id', $branch->id);
		$cc_accounts->addCondition('created_at','<',$on_date);
		$cc_accounts->scheme_join->addField('Interest');

		if($test_account)
			$cc_accounts->addCondition('id',$test_account->id);
 		
 		$a=1;
 		$this->api->markProgress('accounts',$a,$cc_accounts->count()->getOne());
		foreach ($cc_accounts as $accounts_array) {
			$cc_accounts->postInterestEntry($on_date);
	 		$this->api->markProgress('accounts',++$a,null,$cc_accounts['AccountNumber']);
		}
		
		// $this->resetCurrentInterest($branch, $test_account);
	}

	function yearly($branch=null,$on_date=null,$test_account=null){
		throw $this->exception('Renewal Chrges to be done');
	}
}