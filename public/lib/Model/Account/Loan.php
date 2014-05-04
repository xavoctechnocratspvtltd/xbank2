<?php
class Model_Account_Loan extends Model_Account{
	
	function init(){
		parent::init();

		$this->getElement('scheme_id')->getModel()->addCondition('SchemeType','Loan');
		$this->addCondition('SchemeType','Loan');

		$this->getElement('Amount')->caption('Loan Amount');
		//$this->add('dynamic_model/Controller_AutoCreator');
	}
}