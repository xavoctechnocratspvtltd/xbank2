<?php
class Model_Account_Recurring extends Model_Account{
	
	

	function init(){
		parent::init();

		$this->addCondition('SchemeType','Recurring');

		//$this->add('dynamic_model/Controller_AutoCreator');
	}
}