<?php
class Model_Account_CC extends Model_Account{
	public $transaction_deposit_type = TRA_CC_ACCOUNT_AMOUNT_DEPOSIT; 
	function init(){
		parent::init();

		$this->addCondition('SchemeType','CC');

		$this->getElement('scheme_id')->getModel()->addCondition('SchemeType','CC');
		$this->getElement('Amount')->caption('CC Limit');
		//$this->add('dynamic_model/Controller_AutoCreator');
	}
}