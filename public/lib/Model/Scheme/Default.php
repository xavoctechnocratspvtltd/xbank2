<?php
class Model_Scheme_Default extends Model_Scheme {
	
	public $loanType = true;
	public $schemeType = ACCOUNT_TYPE_DEFAULT;
	public $schemeGroup = ACCOUNT_TYPE_DEFAULT;

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
		
		$this->getElement('balance_sheet_id')->caption('Head');
		$this->getElement('AccountOpenningCommission')->caption('Account Commissions(in %)');


		$this->addCondition('SchemeType',$this->schemeType);

		$this->addHook('beforeInsert',$this);

		//$this->add('dynamic_model/Controller_AutoCreator');
	}


	function beforeInsert($model){
		if($model['isDepriciable']){
			if(!$model['DepriciationPercentBeforeSep'])
				throw $this->exception('Please Specify value','ValidityCheck')->setField('DepriciationPercentBeforeSep');
			if(!$model['DepriciationPercentAfterSep'])
				throw $this->exception('Please Specify value','ValidityCheck')->setField('DepriciationPercentAfterSep');
		}		
	}
	function getDefaultAccounts(){
		return array(
				// These default accounts are required when a new branch created not when a new scheme of this type is created
				// array('under_scheme'=>CASH_ACCOUNT,'intermediate_text'=>"",'Group'=>CASH_ACCOUNT,'PAndLGroup'=>CASH_ACCOUNT),
				// array('under_scheme'=>BRANCH_TDS_ACCOUNT,'intermediate_text'=>"",'Group'=>BRANCH_TDS_ACCOUNT,'PAndLGroup'=>BRANCH_TDS_ACCOUNT),
			);
	}
}