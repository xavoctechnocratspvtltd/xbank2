<?php
class Model_Scheme_SavingAndCurrent extends Model_Scheme {
	
	public $loanType = true;
	public $schemeType = ACCOUNT_TYPE_BANK;
	public $schemeGroup = ACCOUNT_TYPE_BANK;

	function init(){
		parent::init();

		$this->getElement('ProcessingFeesinPercent')->destroy();
		$this->getElement('InterestMode')->destroy();
		$this->getElement('InterestRateMode')->destroy();
		$this->getElement('LoanType')->destroy();
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
		$this->getElement('MaturityPeriod')->destroy();
		$this->getElement('AccountOpenningCommission')->destroy();
		$this->getElement('isDepriciable')->destroy();
		$this->getElement('DepriciationPercentBeforeSep')->destroy();
		$this->getElement('DepriciationPercentAfterSep')->destroy();
		
		$this->getElement('balance_sheet_id')->caption('Head')->getModel()->addCondition('name','Deposits - Liabilities');

		$this->addCondition('SchemeType',$this->schemeType);

		//$this->add('dynamic_model/Controller_AutoCreator');
	}

	function getDefaultAccounts(){
		return array(
				array('under_scheme'=>"Indirect Expenses",'intermediate_text'=>"Commission Paid On",'Group'=>'Commission Paid On Saving and Current','PAndLGroup'=>'Commission Paid On Deposit'),
				array('under_scheme'=>"Indirect Expenses",'intermediate_text'=>"Interest Paid On",'Group'=>'Interest Paid On Saving and Current','PAndLGroup'=>'Interest Paid On Deposit'),
				array('under_scheme'=>"Indirect Income",'intermediate_text'=>"Minimum Balance Charge Received On",'Group'=>'Minimum Balance Charge Received On Saving and Current','PAndLGroup'=>'Minimum Balance Charge Received On Saving and Current'),
				array('under_scheme'=>"Indirect Income",'intermediate_text'=>"ChequeBook Charge Received On",'Group'=>'ChequeBook Charge Received On Saving and Current','PAndLGroup'=>'ChequeBook Charge Received On Saving and Current'),
				array('under_scheme'=>"Indirect Income",'intermediate_text'=>"Statement Charge Received On",'Group'=>'Statement Charge Received On Saving and Current','PAndLGroup'=>'Statement Charge Received On Saving and Current'),
			);
	}

	function halfYearly($branch=null){
		if(!$branch) $branch=$this->api->current_branch;

		$this->resetCurrentInterest($branch);

		$sbca_accounts = $this->add('Model_Account_SavingAndCurrent');
		$sbca_account->addCondition('ActiveStatus',true);
		$sbca_account->addCondition('branch_id',$branch->id);
		$sbca_account->addCondition('created_at','<',$this->api->today);
		foreach ($sbca_account as $accounts_array) {
			$sbca_account->applyHalfYearlyInterest($this->api->today);
		}
	}

	function resetCurrentInterest($branch=null){
		if(!$branch) $branch=$this->api->current_branch;

		$accounts = $this->add('Model_Account');
		$accounts->addCondition('SchemeType',ACCOUNT_TYPE_BANK);
		$accounts->addCondition('branch_id',$branch->id);

		$accounts->_dsql()
			// May need to uncomment this for first closing through this application version
			// ->set('LastCurrentInterestUpdatedAt',$this->api->db->dsql()->expr("DATE_ADD('" . $this->api->today . "',INTERVAL -1 MONTH)"))
			->set('CurrentInterest',0)
			;
		$accounts->_dsql()->update();
	}
}