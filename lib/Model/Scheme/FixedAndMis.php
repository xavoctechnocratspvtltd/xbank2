<?php
class Model_Scheme_FixedAndMis extends Model_Scheme {
	
	public $loanType = true;
	public $schemeType = ACCOUNT_TYPE_FIXED;
	public $schemeGroup = ACCOUNT_TYPE_FIXED;

	function init(){
		parent::init();
		
		$this->getElement('type')->group('a~2~Basic Details')->mandatory(true);
		$this->getElement('name')->group('a~8~Basic Details');
		$this->getElement('ActiveStatus')->group('a~2~Basic Details');
		
		$this->getElement('Interest')->group('b~4~Product Details')->mandatory(true);
		$this->getElement('InterestToAnotherAccount')->group('b~4~Product Details')->mandatory(true);
		$this->getElement('MaturityPeriod')->group('b~4~Product Details')->mandatory(true)->type('Number');
		// $this->getElement('ProcessingFees')->group('b~2~Product Details')->mandatory(true)->type('Number');
		// $this->getElement('ProcessingFeesinPercent')->group('b~2~Product Details');
		
		$this->getElement('AccountOpenningCommission')->group('d~6~Commission')->mandatory(true);
		$this->getElement('CRPB')->group('d~6~Commission')->mandatory(true);
		

		$this->getElement('balance_sheet_id')->group('c~3~Accounts Details')->mandatory(true);
		$this->getElement('SchemeGroup')->group('c~3~Accounts Details')->mandatory(true);
		$this->getElement('MinLimit')->group('c~3~Accounts Details')->mandatory(true)->defaultValue(0);
		$this->getElement('MaxLimit')->group('c~3~Accounts Details')->mandatory(true)->defaultValue(-1);

		$this->getElement('percent_loan_on_deposit')->group('e~6~Loan Against Scheme');
		$this->getElement('no_loan_on_deposit_till')->group('e~6~Loan Against Scheme')->hint('in days');
		$this->getElement('pre_mature_interests')->group('f~12~Pre Maturity Options')->hint('days:interest,days:interest like 180:12,365:8,1095:7');

		$this->getElement('type')->setValueList(array('FD'=>'FD','MIS'=>'MIS'));

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
		$this->getElement('MaturityPeriod')->caption('Maturity (in Days)');
		$this->getElement('AccountOpenningCommission')->caption('Commission')->type('Number');
		$this->getElement('InterestToAnotherAccount')->caption('Interest To Account (check if interest to be posted to other account)');

		$this->getElement('balance_sheet_id')->caption('Head')->getModel()->addCondition('name','Deposits - Liabilities');
		$this->addCondition('SchemeType',$this->schemeType);

		$this->addHook('beforeSave',$this);

		//$this->add('dynamic_model/Controller_AutoCreator');
	}


	function beforeSave(){
		if(($this['type']=='MIS' and !$this['InterestToAnotherAccount']) OR ($this['type']=='FD' and $this['InterestToAnotherAccount'])){
			throw $this->exception('Type and Interest to another account is not matching well','ValidityCheck')->setField('type');
		}
	}

	function getDefaultAccounts(){
		return array(
				array('under_scheme'=>"Indirect Expenses",'intermediate_text'=>"Commission Paid On",'Group'=>'Commission Paid On FD and MIS','PAndLGroup'=>'Commission Paid On Deposit'),
				array('under_scheme'=>"Indirect Expenses",'intermediate_text'=>"Interest Paid On",'Group'=>'Interest Paid On FD and MIS','PAndLGroup'=>'Interest Paid On Deposit'),
				// array('under_scheme'=>"Indirect Expenses",'intermediate_text'=>"Collection Charges Paid On",'Group'=>'Collection Charges Paid On FD and MIS','PAndLGroup'=>'Collection Charges Paid On Deposit'),
				array('under_scheme'=>"Provision",'intermediate_text'=>"Interest Provision On",'Group'=>'Interest Provision On FD and MIS','PAndLGroup'=>'Interest Payable On Deposit'),
				array('under_scheme'=>"Provision",'intermediate_text'=>"Commission Payable On",'Group'=>'Commission Payable On FD and MIS','PAndLGroup'=>'Commission Payable Paid On Deposit'),
			);
	}

	function daily($branch=null,$on_date=null,$test_account=null){

		if ( !$branch ) $branch=$this->api->current_branch;
		// Doing Daily as on each year interest percentage changes
		// ON the year complete date give old percentage style provision and marks
		// lastinterestgiven date .. next time in month
		// remaining days will be provisioned as per next percentage .... 

		$active_fd_accounts = $this->add('Model_Active_Account_FixedAndMis',array('table_alias'=>'main_table'));
		$scheme_join = $active_fd_accounts->join('schemes','scheme_id');

		$scheme_join->addField('InterestToAnotherAccount');
		$scheme_join->addField('Interest');

		$active_fd_accounts->addCondition('MaturedStatus',false);
		$active_fd_accounts->addCondition('created_at','<',$on_date);
		$active_fd_accounts->addCondition('branch_id',$branch->id);
		$active_fd_accounts->addCondition('DefaultAC',false);

		if($test_account) $active_fd_accounts->addCondition('id',$test_account->id);
		
		$active_fd_accounts->addExpression('days_collapsed')->set('DATEDIFF('.$active_fd_accounts->table_alias.'.created_at,"'.$on_date.'")');

		// $active_fd_accounts->addCondition('maturity_date','like',$on_date. '%');
		$active_fd_accounts->_dsql()->having($active_fd_accounts->dsql()->expr('((days_collapsed-1) % 365 = 0)') . ' OR maturity_date like "'.$this->api->nextDate($on_date). '%"');
		
		foreach ($active_fd_accounts as $active_fd_accounts_array) {
			if($active_fd_accounts['InterestToAnotherAccount']){
				// This is MIS
				$maturity_day=false;
				if(strtotime(date('Y-m-d',strtotime($on_date))) == strtotime(date('Y-m-d',strtotime($this->api->previousDate($active_fd_accounts['maturity_date'])))) ){
					$maturity_day=true;
				}
				$active_fd_accounts->interstToAnotherAccountEntry($on_date,$maturity_day,$minus_one_day=true);
			}else{
				// This is FD
				$maturity_day=false;
				if(strtotime(date('Y-m-d',strtotime($on_date))) == strtotime(date('Y-m-d',strtotime($this->api->previousDate($active_fd_accounts['maturity_date'])))) ){
					$maturity_day=true;
				}				

				$active_fd_accounts->doInterestProvision($on_date,$maturity_day);
				if($maturity_day ){
					$active_fd_accounts->revertProvision($on_date);
					$active_fd_accounts->settleAccessOrLess($on_date,$this->app->nextDate($on_date));
					$active_fd_accounts->markMature($on_date);
				}
			}
		}

		$this->api->markProgress('Doing_Provision',null);
	}

	function monthly($branch=null,$on_date=null,$test_account=null){
		if ( !$branch ) $branch=$this->api->current_branch;

		// Do Provisions
		$active_fd_accounts = $this->add('Model_Active_Account_FixedAndMis');
		$scheme_join = $active_fd_accounts->join('schemes','scheme_id');

		$scheme_join->addField('InterestToAnotherAccount');
		$scheme_join->addField('Interest');

		$active_fd_accounts->addCondition('MaturedStatus',false);
		$active_fd_accounts->addCondition('DefaultAC',false);
		// $active_fd_accounts->addCondition('InterestToAnotherAccount',false);
		$active_fd_accounts->addCondition('created_at','<',$on_date);
		$active_fd_accounts->addCondition('branch_id',$branch->id);

		if($test_account) $active_fd_accounts->addCondition('id',$test_account->id);

		foreach ($active_fd_accounts as $active_fd_accounts_array) {
			if($active_fd_accounts['InterestToAnotherAccount']){
				// This is MIS
				try{
					$active_fd_accounts->interstToAnotherAccountEntry($on_date);
				}catch(Exception $e){
					throw $e;
					echo $active_fd_accounts['AccountNumber'].' errored <br/>';
				}
			}else{
				// This is FD
				try{
					$active_fd_accounts->doInterestProvision($on_date);
				}catch(Exception $e){
					throw $e;
					echo $active_fd_accounts['AccountNumber'].' errored <br/>';
				}
			}
		}

		$active_fd_accounts = null;
		unset($active_fd_accounts);

	}

	function halfYearly($branch=null,$on_date=null,$test_account=null){
	}

	function yearly($branch=null,$on_date=null,$test_account=null){
		if ( !$branch ) $branch=$this->api->current_branch;
		
		$active_fd_accounts = $this->add('Model_Active_Account_FixedAndMis');
		$scheme_join = $active_fd_accounts->join('schemes','scheme_id');

		$scheme_join->addField('InterestToAnotherAccount');
		$scheme_join->addField('Interest');

		$active_fd_accounts->addCondition('MaturedStatus',false);
		$active_fd_accounts->addCondition('InterestToAnotherAccount',false);
		$active_fd_accounts->addCondition('created_at','<',$on_date);
		$active_fd_accounts->addCondition('branch_id',$branch->id);

		if($test_account) $active_fd_accounts->addCondition('id',$test_account->id);
		$total_count = $active_fd_accounts->count()->getOne();
		$i=1;
		foreach ($active_fd_accounts as $active_fd_accounts_array) {
			// $active_fd_accounts->doInterestProvision($on_date); // Already happening in Monthly so mute it here
			$active_fd_accounts->revertProvision($on_date);
			if($i%100 ==0) gc_collect_cycles();
			$this->api->markProgress('Reverting_Provision',$i++,$active_fd_accounts_array['AccountNumber'],$total_count);
		}
		$this->api->markProgress('Reverting_Provision',null);
	}
}