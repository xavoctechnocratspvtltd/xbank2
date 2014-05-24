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
		$this->getElement('type')->destroy();
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
		$this->getElement('MaturityPeriod')->caption('Period of Maturity for FD, MIS, RD, DDS (in months)');

		

		$this->addCondition('SchemeType',$this->schemeType);

		//$this->add('dynamic_model/Controller_AutoCreator');
	}

	function getDefaultAccounts(){
		return array(
			array('under_scheme'=>"Indirect Expenses",'intermediate_text'=>"Commission Paid On",'Group'=>'Commission Paid On DDS','PAndLGroup'=>'Commission Paid On Deposit'),
			array('under_scheme'=>"Indirect Expenses",'intermediate_text'=>"Interest Paid On",'Group'=>'Interest Paid On DDS','PAndLGroup'=>'Interest Paid On Deposit'),
			// "Indirect Expenses"=>"Collection Charges Paid On",
			// "Provision"=>array('intermediate_text'=>"Commission Payable On",'Group'=>'Commission Payable On'),
			// "Provision"=>array('intermediate_text'=>"Interest Payable On",'Group'=>'Interest Payable On'),
			// "Provision"=>"Collection Payable On"

			);
	}

	function daily($branch=null,$on_date=null,$test_account=null){
		if(!$branch) $branch = $this->api->current_branch;
		if(!$on_date) $on_date = $this->api->today;

		$matured_dds_accounts = $this->add('Model_Account_DDS');
		// $matured_dds_accounts->scheme_join->addField('MaturityPeriod');
		$matured_dds_accounts->addCondition('ActiveStatus',true);
		$matured_dds_accounts->addCondition('MaturedStatus',false);
		$matured_dds_accounts->addCondition('branch_id',$branch->id);
		$matured_dds_accounts->addCondition("maturity_date",$on_date);

		if($test_account) $matured_dds_accounts->addCondition('id',$test_account->id);

		foreach ($matured_dds_accounts as $acc) {
			$matured_dds_accounts->postInterestEntry($on_date);
			$matured_dds_accounts->markMatured($on_date);
		}

	}

	function monthly($branch=null,$on_date=null,$test_account=null){
		if(!$branch) $branch = $this->api->current_branch;
		if(!$on_date) $on_date = $this->api->today;

        $accounts_to_work_on = $this->add('Model_Active_Account_DDS');

        // Compulsory Transactions
        $tr_row_join = $accounts_to_work_on->join('transaction_row.account_id');
        $tr_join = $tr_row_join->join('transactions','transaction_id');
        $tr_type_join = $tr_join->join('transaction_types','transaction_type_id');
        
        // Agent is left joint becoz even in case of no agent the account need to be posted by interest entry
        $agent_join = $accounts_to_work_on->leftJoin('agents','agent_id');
        $agent_account_join = $agent_join->join('accounts','account_id');

        $accounts_to_work_on->scheme_join->addField('AccountOpenningCommission');
        $agent_account_join->addField('agent_AccountNumber','AccountNumber');
        $tr_type_join->addField('transaction_type','name');
        $tr_join->addField('transaction_date','created_at');
        $accounts_to_work_on->addExpression('monthly_credited_amount')->set('SUM('.$tr_row_join->table_alias.'.amountCr)');

        $accounts_to_work_on->addCondition('transaction_type','<>','InterestPostingsInDDSAccounts');
        $accounts_to_work_on->addCondition('transaction_date','>=',$on_date);
        $accounts_to_work_on->addCondition('transaction_date','<',$this->api->nextDate($on_date));
        $accounts_to_work_on->addCondition('branch_id',$branch->id);

        if($test_account) $accounts_to_work_on->addCondition('id',$test_account->id);

        $accounts_to_work_on->_dsql()->group($tr_row_join->table_alias.'.account_id');

        foreach ($accounts_to_work_on as $acc_array) {
        	$accounts_to_work_on->postAgentCommissionEntry($on_date);
        }

	}

	function halfYearly($branch=null,$on_date=null,$test_account=null){

	}

	function yearly($branch=null,$on_date=null,$test_account=null){
		if(!$branch) $branch = $this->api->current_branch;
		if(!$on_date) $on_date = $this->api->now; 

		$active_dds_accounts = $this->add('Model_Active_Account_DDS');
		$active_dds_accounts->addCondition('MaturedStatus',false);
		$active_dds_accounts->addCondition('branch_id',$branch->id);

		if($test_account) $active_dds_accounts->addCondition('id',$test_account);

		foreach ($active_dds_accounts as $active_dds_accounts_array) {
			$active_dds_accounts->postInterestEntry($on_date)
		}

	}

}