<?php

class Model_BS_TransactionRow extends Model_TransactionRow {
	
	public $from_date=null;
	public $to_date=null;
	public $branch_id = null;

	function init(){
		parent::init();

		$this->transaction_join->destroy();

		// $account_j = $this->join('accounts','account_id');
		// $account_j->addField('scheme_id');

		// $scheme_j = $account_j->join('schemes','scheme_id');
		// $scheme_j->addField('balance_sheet_id');

		// $this->addExpression('balance_sheet_id')->set(function($m,$q){
		// 	$acc= $this->add('Model_BS_Ledger');
		// 	$acc->addCondition('id',$q->getField('account_id'));
		// 	return $acc->fieldQuery('balance_sheet_id');
		// });

		// $this->addExpression('scheme_id')->set(function($m,$q){
		// 	$acc= $this->add('Model_BS_Ledger');
		// 	$acc->addCondition('id',$q->getField('account_id'));
		// 	return $acc->fieldQuery('scheme_id');
		// });
	}
}