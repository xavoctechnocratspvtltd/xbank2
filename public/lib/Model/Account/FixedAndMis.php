<?php
class Model_Account_FixedAndMis extends Model_Account{
	
	public $transaction_deposit_type = TRA_FIXED_ACCOUNT_DEPOSIT;	
	public $default_transaction_deposit_narration = "Amount submited in Saving Account {{AccountNumber}}";	

	function init(){
		parent::init();

		$this->getElement('account_type')->enum(array('FD','MIS'));
		$this->addCondition('SchemeType','FixedAndMis');

		$this->getElement('Amount')->caption('FD/MIS Amount');
		$this->getElement('AccountDisplayName')->caption('Account Name (IF Joint)');
		$this->getElement('scheme_id')->getModel()->addCondition('SchemeType','FixedAndMis');

		$this->addExpression('maturity_date')->set(function($m,$q){
			return "DATE_ADD(DATE(".$m->dsql()->getField('created_at')."), INTERVAL +".$m->scheme_join->table_alias.".MaturityPeriod DAYS)";
		});

		//$this->add('dynamic_model/Controller_AutoCreator');
	}
}