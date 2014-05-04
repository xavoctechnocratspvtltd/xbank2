<?php
class Model_Account_Loan extends Model_Account{
	
	function init(){
		parent::init();

		$this->addCondition('SchemeType','Loan');

		//$this->add('dynamic_model/Controller_AutoCreator');
	}
}