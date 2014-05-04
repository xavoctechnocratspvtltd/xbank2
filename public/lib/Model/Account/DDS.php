<?php
class Model_Account_DDS extends Model_Account{
	
	

	function init(){
		parent::init();

		$this->addCondition('SchemeType','DDS');

		//$this->add('dynamic_model/Controller_AutoCreator');
	}
}