<?php
class Model_Account_Default extends Model_Account{
	

	function init(){
		parent::init();

		$this->addCondition('SchemeType','Default');
		//$this->add('dynamic_model/Controller_AutoCreator');
	}

	function updateForm($form){
		$form->addField('CheckBox','do_calculations');
	}

}