<?php
class Model_Account_FixedAndMis extends Model_Account{
	

	function init(){
		parent::init();

		$this->addCondition('SchemeType','FixedAndMis');

		//$this->add('dynamic_model/Controller_AutoCreator');
	}
}