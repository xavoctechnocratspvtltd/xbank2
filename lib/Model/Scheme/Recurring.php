<?php
class Model_Scheme_Recurring extends Model_Scheme {
	
	public $loanType = true;
	public $schemeType = 'Recurring';
	public $schemeGroup = 'Recurring';

	function init(){
		parent::init();

		$this->getElement('type')->group('a~2~Basic Details')->mandatory(true);
		$this->getElement('name')->group('a~8~Basic Details');
		$this->getElement('ActiveStatus')->group('a~2~Basic Details');
		
		$this->getElement('NumberOfPremiums')->group('b~3~Product Details');
		$this->getElement('Interest')->group('b~3~Product Details')->mandatory(true);
		$this->getElement('MaturityPeriod')->group('b~3~Product Details')->mandatory(true)->type('Number');
		$this->getElement('PremiumMode')->group('b~3~Product Details')->mandatory(true)->type('Number');
		
		$this->getElement('balance_sheet_id')->group('c~3~Accounts Details')->mandatory(true);
		$this->getElement('SchemeGroup')->group('c~3~Accounts Details')->mandatory(true);
		$this->getElement('MinLimit')->group('c~3~Accounts Details')->mandatory(true)->defaultValue(0);
		$this->getElement('MaxLimit')->group('c~3~Accounts Details')->mandatory(true)->defaultValue(-1);

		$this->getElement('CRPB')->group('d~12~Commission')->mandatory(true);
		$this->getElement('AccountOpenningCommission')->group('d~12~bl')->mandatory(true);
		$this->getElement('CollectorCommissionRate')->group('d~12~bl')->mandatory(true);

		$this->getElement('ProcessingFeesinPercent')->destroy();
		$this->getElement('InterestMode')->destroy();
		$this->getElement('InterestRateMode')->destroy();
		$this->getElement('type')->defaultValue('Recurring');
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
		$this->getElement('MaturityPeriod')->caption('Maturity (Months)');

		$this->addCondition('SchemeType',$this->schemeType);

		//$this->add('dynamic_model/Controller_AutoCreator');
	}

	function getDefaultAccounts(){
		return array(
			array('under_scheme'=>"Indirect Expenses",'intermediate_text'=>"Commission Paid On",'Group'=>'Commission Paid On RD','PAndLGroup'=>'Commission Paid On Deposit'),
			array('under_scheme'=>"Indirect Expenses",'intermediate_text'=>"Interest Paid On",'Group'=>'Interest Paid On RD','PAndLGroup'=>'Interest Paid On Deposit'),
			array('under_scheme'=>"Indirect Expenses",'intermediate_text'=>"Collection Charges Paid On",'Group'=>'Collection Charges Paid On RD','PAndLGroup'=>'Collection Charges Paid On Deposit')
		);
	}

	function daily($branch=null,$on_date=null, $test_account=null){
		if(!$branch) $branch = $this->api->current_branch;
		if(!$on_date) $on_date = $this->api->now;

		$all_todays_matured_Accounts = $this->add('Model_Active_Account_Recurring');
		$all_todays_matured_Accounts->addCondition('branch_id',$branch->id);
		$all_todays_matured_Accounts->addCondition('maturity_date',$on_date);
		$all_todays_matured_Accounts->addCondition('MaturedStatus',false);
		$all_todays_matured_Accounts->addCondition('branch_id',$branch->id);

		if($test_account) $all_todays_matured_Accounts->addCondition('id',$test_account->id);

		foreach ($all_todays_matured_Accounts as $acc_array) {
			$all_todays_matured_Accounts->markMatured($on_date); // peying interest in there as well
		}

	}
	
	function monthly( $branch=null, $on_date=null, $test_account=null ) {
	
		$allaccounts_with_thismonth_duedate = $this->add('Model_Active_Account_Recurring');
		$premium_join = $allaccounts_with_thismonth_duedate->join('premiums.account_id');
		$premium_join->addField('Paid');
		$premium_join->addField('DueDate');
		$allaccounts_with_thismonth_duedate->addCondition('Paid',false);
		$allaccounts_with_thismonth_duedate->addCondition('DueDate','<=',$on_date);
		$allaccounts_with_thismonth_duedate->_dsql()->group('AccountNumber');

		if($test_account) $allaccounts_with_thismonth_duedate->addCondition('id',$test_account->id);

		foreach ($allaccounts_with_thismonth_duedate as $junk) {
			$allaccounts_with_thismonth_duedate->reAdjustPaidValue($on_date);
		}
	}

	function quarterly( $branch=null, $on_date=null, $test_account=null ) {

	}

	function halfYearly( $branch=null, $on_date=null, $test_account=null ) {
	}


	function yearly($branch, $on_date=null,$test_account=null){
		if(!$branch) $branch = $this->api->current_branch;
		if(!$on_date) $on_date = $this->api->now;

		$fy = $this->api->getFinancialYear($on_date);

		$all_accounts_paid_in_this_year = $this->add('Model_Active_Account_Recurring');
		$premium_join = $all_accounts_paid_in_this_year->join('premiums.account_id');
		$premium_join->addField('PaidOn');


		$all_accounts_paid_in_this_year->addCondition('PaidOn','>=',$fy['start_date']);
		$all_accounts_paid_in_this_year->addCondition('PaidOn','<',$this->api->nextDate($fy['end_date']));
		$all_accounts_paid_in_this_year->addCondition('MaturedStatus',false);
		$all_accounts_paid_in_this_year->addCondition('branch_id',$branch->id);

		if($test_account) $all_accounts_paid_in_this_year->addCondition('id',$test_account->id);

		foreach ($all_accounts_paid_in_this_year as $junk) {
			$all_accounts_paid_in_this_year->payInterest();						
		}

	}
}