<?php
class Model_Account_Recurring extends Model_Account{
	
	

	function init(){
		parent::init();

		$this->addCondition('SchemeType','Recurring');
		$this->getElement('scheme_id')->getModel()->addCondition('SchemeType','Recurring');
		$this->getElement('Amount')->caption('RECURRING amount (premium)');
		//$this->add('dynamic_model/Controller_AutoCreator');
	}
}