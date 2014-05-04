<?php
class Model_Account_SavingAndCurrent extends Model_Account{
	
	

	function init(){
		parent::init();

		$this->addCondition('SchemeType','SavingAndCurrent');

		//$this->add('dynamic_model/Controller_AutoCreator');
	}
}