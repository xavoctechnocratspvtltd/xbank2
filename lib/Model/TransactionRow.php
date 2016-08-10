<?php
class Model_TransactionRow extends Model_Table {
	var $table= "transaction_row";
	public $transaction_join;
	function init(){
		parent::init();

		$this->hasOne('Transaction','transaction_id');
		$this->hasOne('Account','account_id');
		$this->hasOne('Scheme','transaction_row_id');
		$this->hasOne('BalanceSheet','transaction_row_id');
		
		$this->addField('amountDr')->caption('Debit')->type('money');
		$this->addField('amountCr')->caption('Credit')->type('money');
		$this->addField('side');
		$this->addField('accounts_in_side')->type('int');
		
		$this->addField('created_at')->type('datetime')->defaultValue($this->app->now);

		$this->transaction_join = $join_transaction = $this->join('transactions','transaction_id');
		$join_transaction->addField('voucher_no');
		$join_transaction->addField('Narration');
		// $join_transaction->addField('created_at');
		$join_transaction->hasOne('TransactionType','transaction_type_id');
		$join_transaction->hasOne('Branch','branch_id');
		$join_transaction->hasOne('Account','reference_id');

		$this->setOrder('created_at');

		//$this->add('dynamic_model/Controller_AutoCreator');
	}

	function account(){
		return $this->ref('account_id');
	}

	function forceDelete(){
		if($this['amountCr'])
			$this->ref('account_id')->creditOnly(-1 * $this['amountCr']);
		if($this['amountDr'])
			$this->ref('account_id')->debitOnly( -1 * $this['amountDr']);

		$this->delete();
	}
}