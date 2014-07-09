<?php
class Model_Scheme_Loan extends Model_Scheme {

	public $loanType = true;
	public $schemeType = ACCOUNT_TYPE_LOAN;
	public $schemeGroup = ACCOUNT_TYPE_LOAN;

	function init(){
		parent::init();

		$this->getElement('ProcessingFeesinPercent')->caption('Check if Processing Fee in %');
		$this->getElement('balance_sheet_id')->caption('Head');
		$this->getElement('type')->enum(explode(",",LOAN_TYPES));

		$this->getElement('InterestMode')->destroy();
		$this->getElement('InterestRateMode')->destroy();
		$this->getElement('AccountOpenningCommission')->destroy();
		$this->getElement('Commission')->destroy();
		$this->getElement('PostingMode')->destroy();
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
		// $this->getElement('AccountOpenningCommission')->destroy();
		
		$this->addCondition('SchemeType',$this->schemeType);

		$this->addHook('beforeSave',array($this,'beforeLoanSchemeSave'));
		// $this->addHook('schemeFormSubmitted',$this);
		
		//$this->add('dynamic_model/Controller_AutoCreator');
	}



	function beforeLoanSchemeSave(){
		if(!$this['type'])
			throw $this->exception('Please Specify Loan type', 'ValidityCheck')->setField('LoanType');
	}


	function getDefaultAccounts(){
		return array(
				array('under_scheme'=>"Indirect Income",'intermediate_text'=>"Interest Received On",'Group'=>'Interest Received On {{Loan}}','PAndLGroup'=>'Interest Received On Loan'),
				array('under_scheme'=>"Indirect Income",'intermediate_text'=>"Processing Fee Received On",'Group'=>'Processing Fee Received On {{Loan}}','PAndLGroup'=>'Processing Fee Received On Loan'),
				array('under_scheme'=>"Indirect Income",'intermediate_text'=>"Penalty Due To Late Payment On",'Group'=>'Penalty Due To Late Payment On {{Loan}}','PAndLGroup'=>'Penalty Due To Late Payment On Loan'),
				array('under_scheme'=>"Indirect Income",'intermediate_text'=>"For Close Account On",'Group'=>'For Close Account On {{Loan}}','PAndLGroup'=>'For Close Account On Loan'),
				array('under_scheme'=>"Indirect Income",'intermediate_text'=>"Time Over Charge On",'Group'=>'Time Over Charge On {{Loan}}','PAndLGroup'=>'Time Over Charge On Loan'),
				array('under_scheme'=>"Indirect Income",'intermediate_text'=>"Conveyence Charge Received On",'Group'=>'Conveyence Charge Received On {{Loan}}','PAndLGroup'=>'Conveyence Charge Received On Loan'),
				array('under_scheme'=>"Indirect Income",'intermediate_text'=>"Rent Charge Received On",'Group'=>'Rent Charge Received On {{Loan}}','PAndLGroup'=>'Rent Charge Received On Loan'),
				array('under_scheme'=>"Indirect Income",'intermediate_text'=>"Legal Charge Received On",'Group'=>'Legal Charge Received On {{Loan}}','PAndLGroup'=>'Legal Charge Received On Loan'),
			);
	}

	function daily($branch= null,$on_date=null,$test_account=null){
		if(!$on_date) $on_date = $this->api->now;
		if(!$branch) $branch = $this->api->current_branch;

		$this->putPaneltiesOnAllUnpaidLoanPremiums($branch,$on_date,$test_account);
		
		$loan_accounts  = $this->add('Model_Active_Account_Loan');
		
		$loan_accounts->scheme_join->addField('Interest');
		$loan_accounts->scheme_join->addField('NumberOfPremiums');
		$loan_accounts->scheme_join->addField('ReducingOrFlatRate');

		$loan_accounts->leftJoin('premiums.account_id')
						->addField('DueDate');

		$loan_accounts->addCondition('DueDate','like',$on_date.' %');
		$loan_accounts->addCondition('branch_id',$branch->id);

		$loan_accounts->addExpression('due_panelty')->set(function($m,$q)use($on_date){
			return $m->refSQL('Premium')->addCondition('PaneltyCharged','<>','PaneltyPosted')->addCondition('DueDate','<',$on_date)->sum($m->dsql()->expr('PaneltyCharged - PaneltyPosted'));
		});

		if($test_account) $loan_accounts->addCondition('id',$test_account->id);

		foreach ($loan_accounts as $acc_array) {
			$loan_accounts->postInterestEntry($on_date);
			if($loan_accounts['due_panelty'] > 0)
				$loan_accounts->postPanelty($on_date);
		}

				$p_m=$this->add('Model_Premium');
                $p_m->_dsql()->set('PaneltyCharged','PaneltyPosted');
                if($test_account) $p_m->_dsql()->where('account_id',$test_account->id);
                $p_m->_dsql()->update();
	}

	// Not related with Any account ... general for all accounts
	function putPaneltiesOnAllUnpaidLoanPremiums($branch=null,$on_date=null,$test_account=null){
		if(!$on_date) $on_date = $this->api->now;
		if(!$branch) $branch = $this->api->current_branch;

		$premiums = $this->add('Model_Premium');
		$account_join = $premiums->leftJoin('accounts','account_id');
		$account_join->addField('branch_id');
		$dealer_join = $account_join->leftJoin('dealers','dealer_id');
		$dealer_join->addField('loan_panelty_per_day');

		$premiums->addCondition('DueDate','<',$on_date);
		$premiums->addCondition('PaneltyCharged','<',$this->api->db->dsql()->expr('loan_panelty_per_day * 30'));
		$premiums->addCondition('Paid',false);
		$premiums->addCondition('branch_id',$branch->id);

		if($test_account) $premiums->addCondition('account_id',$test_account->id);

		$premiums->_dsql()->set('PaneltyCharged',$this->api->db->dsql()->expr('PaneltyCharged +'. $dealer_join->table_alias.'.loan_panelty_per_day'));
		if($test_account) $premiums->_dsql()->where('account_id',$test_account->id);
                $premiums->_dsql()->update();
	}



	function monthly($branch=null, $on_date=null,$test_account=null){
		if(!$branch) $branch = $this->api->current_branch;
		if(!$on_date) $on_date = $this->api->now;


	}

	function halfYearly( $branch=null, $on_date=null, $test_account=null ) {
	}

	function yearly( $branch=null, $on_date=null, $test_account=null ) {
	}

}