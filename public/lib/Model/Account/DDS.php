<?php
class Model_Account_DDS extends Model_Account{
	
	

	function init(){
		parent::init();

		$this->addCondition('SchemeType','DDS');
		$this->getElement('scheme_id')->getModel()->addCondition('SchemeType','DDS');
		$this->getElement('Amount')->caption('DDS amount (in multiples of Rs.300 like 300, 600, 900....3000 etc.)');

		//$this->add('dynamic_model/Controller_AutoCreator');
	}
}