<?php
class Model_Memorandum_Transaction extends Model_Table {
	var $table= "memorandum_transactions";

	function init(){
		parent::init();

		$this->hasOne('Staff','staff_id')->display(['form'=>'autocomplete/Basic']);
		$this->hasOne('Branch','branch_id')->display(['form'=>'autocomplete/Basic']);
		// used for reference for memorandom transaction is done
		$this->hasOne('Account','reference_id')->display(['form'=>'autocomplete/Basic']);

		$this->addField('name'); //it may to invoice no or
		$this->addField('transaction_type'); // visiting charge
		$this->addField('narration')->type('text');
		$this->addField('created_at')->type('datetime')->defaultValue($this->api->now);

		$this->hasMany('Memorandum_TransactionRow','memorandum_transaction_id');

		$this->addExpression('cr_sum')->set(function($m,$q){
			return $m->refSQL('Memorandum_TransactionRow')->sum('amountCr');
		});

		$this->addExpression('dr_sum')->set(function($m,$q){
			return $m->refSQL('Memorandum_TransactionRow')->sum('amountDr');
		});
	}
}