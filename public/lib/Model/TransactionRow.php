<?php
class Model_TransactionRow extends Model_Table {
	var $table= "transaction_row";
	function init(){
		parent::init();

		$this->hasOne('Transaction','transaction_id');
		$this->hasOne('Account','account_id');
		$this->addField('amountDr')->type('money');
		$this->addField('amountCr')->type('money');
		$this->addField('side');
		$this->addField('accounts_in_side')->type('int');
		//$this->add('dynamic_model/Controller_AutoCreator');
	}
}