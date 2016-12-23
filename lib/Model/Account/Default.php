<?php
class Model_Account_Default extends Model_Account{
	
	public $transaction_deposit_type = TRA_DEFAULT_ACCOUNT_DEPOSIT_ENTRY;	
	public $default_transaction_deposit_narration = "Amount submited in Default Account {{AccountNumber}}";	


	function init(){
		parent::init();

		$this->addCondition('SchemeType','Default');
		$this->getElement('account_type')->defaultValue('Default');
		$this->getElement('member_id')->mandatory(false);
		//$this->add('dynamic_model/Controller_AutoCreator');
	}

	function updateForm($form){
		$form->addField('CheckBox','do_calculations');
	}

}