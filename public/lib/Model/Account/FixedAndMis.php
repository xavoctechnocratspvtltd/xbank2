<?php
class Model_Account_FixedAndMis extends Model_Account{
	

	function init(){
		parent::init();

		$this->addCondition('SchemeType','FixedAndMis');

		$this->getElement('Amount')->caption('FD/MIS Amount');
		$this->getElement('AccountDisplayName')->caption('Account Name (IF Joint)');
		$this->getElement('scheme_id')->getModel()->addCondition('SchemeType','FixedAndMis');
		//$this->add('dynamic_model/Controller_AutoCreator');
	}
}