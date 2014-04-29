<?php
class Model_Scheme_Loan extends Model_Scheme {
	function init(){
		parent::init();

		$this->getElement('name')->caption('Scheme Name');
		$this->getElement('ProcessingFeesinPercent')->caption('Check if Processing Fee in %');
		$this->getElement('ProcessingFees')->caption('Processing Fees');
		$this->getElement('MaxLimit')->destroy();
		$this->getElement('InterestMode')->destroy();
		$this->getElement('InterestRateMode')->destroy();
		$this->getElement('LoanType')->destroy();
		$this->getElement('AccountOpenningCommission')->destroy();
		// $this->getElement('AccountOpenningCommission')->destroy();

		
		//$this->add('dynamic_model/Controller_AutoCreator');
	}

	function manageForm($form){

		if($form->isSubmitted()){
			$form->js()->univ()->successMessage('HI')->execute();
		}
	}
}