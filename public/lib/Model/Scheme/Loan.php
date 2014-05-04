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
			"Indirect Income"=>"For Close Account On"
			);
	}

}