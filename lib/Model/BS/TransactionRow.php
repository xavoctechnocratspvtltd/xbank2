<?php

class Model_BS_TransactionRow extends MOdel_TransactionRow {
	
	function init(){
		parent::init();

		$this->addExpression('balance_sheet_id')->set(function($m,$q){
			$acc= $this->add('Model_BS_Ledger');
			$acc->addCondition('id',$q->getField('account_id'));
			return $acc->fieldQuery('balance_sheet_id');
		});
	}
}