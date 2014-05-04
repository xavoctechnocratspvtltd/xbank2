<?php
class Model_Account_Loan extends Model_Account{
	
	function init(){
		parent::init();

		$this->getElement('scheme_id')->getModel()->addCondition('SchemeType','Loan');
		$this->addCondition('SchemeType','Loan');

		$this->getElement('RdAmount')->destroy();

		$this->addField('loanAmount','RdAmount');

		$this->add('Order')->move('loanAmount','after','scheme_id')->now();
		
		//$this->add('dynamic_model/Controller_AutoCreator');
	}
}