<?php
class Model_TransactionRow extends Model_Table {
	var $table= "transaction_row";
	function init(){
		parent::init();

		$this->hasOne('Transaction','transaction_id');
		$this->hasOne('Account','account_id');
		$this->addField('amountDr')->caption('Debit')->type('money');
		$this->addField('amountCr')->caption('Credit')->type('money');
		$this->addField('side');
		$this->addField('accounts_in_side')->type('int');

		$join_transaction = $this->leftJoin('transactions','transaction_id');
		$join_transaction->addField('voucher_no');
		$join_transaction->hasOne('Branch','branch_id');
		$join_transaction->addField('Narration');
		$join_transaction->addField('created_at');

		$this->setOrder('created_at');

		//$this->add('dynamic_model/Controller_AutoCreator');
	}

	function delete($revert_accounts_balances=true){
		if($this['amountCr'] AND $revert_accounts_balances)
			$this->ref('account_id')->debitOnly($this['amountCr']);
		if($this['amountDr'] AND $revert_accounts_balances)
			$this->ref('account_id')->creditOnly($this['amountDr']);

		parent::delete();
	}
}