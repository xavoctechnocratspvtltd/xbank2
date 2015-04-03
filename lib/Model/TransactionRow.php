<?php
class Model_TransactionRow extends Model_Table {
	var $table= "transaction_row";
	public $transaction_join;
	function init(){
		parent::init();

		$this->hasOne('Transaction','transaction_id');
		$this->hasOne('Account','account_id');
		$this->addField('amountDr')->caption('Debit')->type('money');
		$this->addField('amountCr')->caption('Credit')->type('money');
		$this->addField('side');
		$this->addField('accounts_in_side')->type('int');

		$this->transaction_join = $join_transaction = $this->leftJoin('transactions','transaction_id');
		$join_transaction->addField('voucher_no');
		$join_transaction->addField('Narration');
		$join_transaction->addField('created_at');
		$join_transaction->hasOne('TransactionType','transaction_type_id');
		$join_transaction->hasOne('Branch','branch_id');

		$this->setOrder('created_at');

		//$this->add('dynamic_model/Controller_AutoCreator');
	}

	function forceDelete(){
		if($this['amountCr'])
			$this->ref('account_id')->debitOnly($this['amountCr']);
		if($this['amountDr'])
			$this->ref('account_id')->creditOnly($this['amountDr']);

		$this->delete();
	}
}