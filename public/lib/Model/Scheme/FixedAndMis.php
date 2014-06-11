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

		$this->getElement('balance_sheet_id')->caption('Head')->getModel()->addCondition('name','Deposits - Liabilities');
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

	function daily($branch=null,$on_date=null,$test_account=null){
		$active_fd_accounts = $this->add('Model_Active_Account_FixedAndMis',array('table_alias'=>'main_table'));
		$scheme_join = $active_fd_accounts->join('schemes','scheme_id');

		$scheme_join->addField('InterestToAnotherAccount');
		$scheme_join->addField('Interest');

		$active_fd_accounts->addCondition('MaturedStatus',false);
		$active_fd_accounts->addCondition('created_at','<',$on_date);
		$active_fd_accounts->addCondition('branch_id',$branch->id);

		if($test_account) $active_fd_accounts->addCondition('id',$test_account->id);
		
		$active_fd_accounts->addExpression('days_collapsed')->set('DATEDIFF('.$active_fd_accounts->table_alias.'.created_at,"'.$on_date.'")');

		// $active_fd_accounts->addCondition('maturity_date','like',$on_date. '%');
		$active_fd_accounts->_dsql()->having($active_fd_accounts->dsql()->expr('days_collapsed % 365 = 0'));
		
		foreach ($active_fd_accounts as $active_fd_accounts_array) {
			if($active_fd_accounts['InterestToAnotherAccount']){
				// This is MIS
				$active_fd_accounts->interstToAnotherAccountEntry($on_date);
			}else{
				// This is FD
				$maturity_day=false;
				$year_completed = true;
				if(strtotime(date('Y-m-d',strtotime($on_date))) == strtotime(date('Y-m-d',strtotime($active_fd_accounts['maturity_date']))) ){
					$maturity_day=true;
				}
				$active_fd_accounts = $active_fd_accounts->doInterestProvision($on_date,$maturity_day,$year_completed);
				if(strtotime(date('Y-m-d',strtotime($on_date))) == strtotime(date('Y-m-d',strtotime($active_fd_accounts['maturity_date']))) ){
					$active_fd_accounts->revertProvision($on_date);
					$active_fd_accounts->markMature();
				}
			}
		}
	}

	function monthly($branch=null,$on_date=null,$test_account=null){
		// Do Provisions
		$active_fd_accounts = $this->add('Model_Active_Account_FixedAndMis');
		$scheme_join = $active_fd_accounts->join('schemes','scheme_id');

		$scheme_join->addField('InterestToAnotherAccount');
		$scheme_join->addField('Interest');

		$active_fd_accounts->addCondition('MaturedStatus',false);
		$active_fd_accounts->addCondition('InterestToAnotherAccount',false);
		$active_fd_accounts->addCondition('created_at','<',$on_date);
		$active_fd_accounts->addCondition('branch_id',$branch->id);

		if($test_account) $active_fd_accounts->addCondition('id',$test_account->id);

		foreach ($active_fd_accounts as $active_fd_accounts_array) {
			if($active_fd_accounts['InterestToAnotherAccount']){
				// This is MIS
				$active_fd_accounts->interstToAnotherAccountEntry($on_date);
			}else{
				// This is FD
				$active_fd_accounts->doInterestProvision($on_date);
			}
		}
	}

	function halfYearly($branch=null,$on_date=null,$test_account=null){
	}

	function yearly($branch=null,$on_date=null,$test_account=null){
		$active_fd_accounts = $this->add('Model_Active_Account_FixedAndMis');
		$scheme_join = $active_fd_accounts->join('schemes','scheme_id');

		$scheme_join->addField('InterestToAnotherAccount');
		$scheme_join->addField('Interest');

		$active_fd_accounts->addCondition('MaturedStatus',false);
		$active_fd_accounts->addCondition('InterestToAnotherAccount',false);
		$active_fd_accounts->addCondition('created_at','<',$on_date);
		$active_fd_accounts->addCondition('branch_id',$branch->id);

		if($test_account) $active_fd_accounts->addCondition('id',$test_account->id);

		foreach ($active_fd_accounts as $active_fd_accounts_array) {
			$active_fd_accounts->doInterestProvision($on_date);
			$active_fd_accounts->revertProvision($on_date);
		}
	}
}