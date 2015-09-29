<?php
class Model_Active_Account_Loan extends Model_Account_Loan{
	function init(){
		parent::init();
		$this->addCondition('ActiveStatus',true);
	}
	
}