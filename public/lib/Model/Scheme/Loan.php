<?php
class Model_Scheme_Loan extends Model_Scheme {

	public $loanType = true;
	public $schemeType = 'Loan';
	public $schemeGroup = 'Loan';

	function init(){
		parent::init();

		$this->getElement('ProcessingFeesinPercent')->caption('Check if Processing Fee in %');
		$this->getElement('balance_sheet_id')->caption('Head');
		
		$this->getElement('InterestMode')->destroy();
		$this->getElement('InterestRateMode')->destroy();
		$this->getElement('LoanType')->destroy();
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

		$this->addHook('schemeFormCreated',$this);
		// $this->addHook('schemeFormSubmitted',$this);
		
		//$this->add('dynamic_model/Controller_AutoCreator');
	}



	function schemeFormCreated($model,$form){
		$form->getElement('SchemeGroup')->set($this->schemeGroup);
	}

	function createNewScheme($values){
		parent::createNewScheme($values);

	}

	function getDefaultAccounts(){
		return array(
			"Indirect Income"=>"Interest Received On",
			"Indirect Income"=>"Processing Fee Received On",
			"Indirect Income"=>"Penalty Due To Late Payment On",
			"Indirect Income"=>"For Close Account On",
			"Indirect Income"=>"Time Over Charge On",
			"Indirect Income"=>"Conveyence Charge Received On",
			"Indirect Income"=>"Rent Charge Received On",
			"Indirect Income"=>"Legal Charge Received On",
			);
	}

	function daily($branch= null,$on_date=null){
		if(!$on_date) $on_date = $this->api->now;
		if(!$branch) $branch = $this->api->current_branch;

		$loan_accounts  = $this->add('Model_Active_Account_Loan');
		$loan_accounts->scheme_join->addField('Interest');
		$loan_accounts->scheme_join->addField('NumberOfPremiums');
		$loan_accounts->scheme_join->addField('ReducingOrFlatRate');

		$loan_accounts->leftJoin('premiums.account_id')
						->addField('DueDate');

		$loan_accounts->addCondition('DueDate','like',$on_date.' %');
		$loan_accounts->addCondition('branch_id',$branch->id);

		foreach ($loan_accounts as $acc_array) {
			$loan_accounts->postInterestEntry($on_date);
		}

		$this->putPaneltiesOnAllLoanAccounts($branch,$on_date);
	}

	function putPaneltiesOnAllLoanAccounts($branch=null,$on_date=null){
		if(!$on_date) $on_date = $this->api->now;
		if(!$branch) $branch = $this->api->current_branch;

		$panelty_accounts = $this->add('Model_Active_Account_Loan');
		$premium_join = $panelty_accounts->leftJoin('premiums.account_id');
		$premium_join->addField('DueDate');
		$premium_join->addField('Paid');
		$dealer_join = $panelty_accounts->leftJoin('dealers','dealer_id');
		$dealer_join->addField('loan_panelty_per_day');

		$panelty_accounts->addCondition('DueDate','<',$on_date);
		$panelty_accounts->addCondition('DueDate','>',date('Y-m-d',strtotime($on_date. ' -1 Month')));
		$panelty_accounts->addCondition('Paid',false);
		$panelty_accounts->addCondition('branch_id',$branch->id);

		$panelty_accounts->_dsql()->set('CurrentInterest',$this->api->db->dsql()->expr('CurrentInterest +'. $dealer_join->table_alias.'.loan_panelty_per_day'));
		$panelty_accounts->_dsql()->update();
	}

	function monthly($branch=null, $on_date=null){
		if(!$branch) $branch = $this->api->current_branch;
		if(!$on_date) $on_date = $this->api->now;

		$accounts_with_panelty = $this->add('Model_Active_Account_Loan');
		$accounts_with_panelty->addCondition('CurrentInterest','<>',0);
		$accounts_with_panelty->addCondition('branch_id',$branch->id);

		foreach ($accounts_with_panelty as $acc) {
			$accounts_with_panelty->postPaneltyTransaction($on_date);
		}

	}

}