<?php
class Model_Scheme_CC extends Model_Scheme {

	public $loanType = true;
	public $schemeType = ACCOUNT_TYPE_CC;
	public $schemeGroup = ACCOUNT_TYPE_CC;

	function init(){
		parent::init();

		$this->getElement('type')->group('a~2~Basic Details')->mandatory(true);
		$this->getElement('name')->group('a~8~Basic Details');
		$this->getElement('ActiveStatus')->group('a~2~Basic Details');
		
		$this->getElement('Interest')->group('b~4~Product Details')->mandatory(true);
		$this->getElement('ProcessingFees')->group('b~4~Product Details')->mandatory(true)->type('Number');
		$this->getElement('ProcessingFeesinPercent')->group('b~4~Product Details');
		
		$this->getElement('balance_sheet_id')->group('c~3~Accounts Details')->mandatory(true);
		$this->getElement('SchemeGroup')->group('c~3~Accounts Details')->mandatory(true);
		$this->getElement('MinLimit')->group('c~3~Accounts Details')->mandatory(true)->defaultValue(0);
		$this->getElement('MaxLimit')->group('c~3~Accounts Details')->mandatory(true)->defaultValue(-1);


		$this->getElement('ProcessingFeesinPercent')->caption('Check if Processing Fee in %');
		$this->getElement('balance_sheet_id')->caption('Head');
		$this->getElement('InterestMode')->destroy();
		$this->getElement('InterestRateMode')->destroy();
		$this->getElement('type')->enum(array('CC'));
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
 		$a_total = $cc_accounts->count()->getOne();
 		$this->api->markProgress('cc accounts',$a,'CC Accounts',$a_total);
		foreach ($cc_accounts as $accounts_array) {
			// echo "working on".$cc_accounts['AccountNumber'];
			$cc_accounts->postInterestEntry($on_date);
	 		$this->api->markProgress('cc accounts',++$a,$cc_accounts['AccountNumber'],$a_total);
		}
		$this->api->markProgress('cc accounts',null);
		// $this->resetCurrentInterest($branch, $test_account);
	}

	function halfYearly(){
		// Nothing to be done
		// throw $this->exception('Half Yearly closing function must be in scheme');
	}

	function yearly($branch=null,$on_date=null,$test_account=null){
		// throw $this->exception('Renewal Charges to be done');
	}
}