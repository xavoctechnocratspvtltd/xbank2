<?php

class View_Voucher extends View{
	public $transaction_id;

	function setModel($transaction){
		$m = parent::setModel($transaction);
		$this->transaction_id = $transaction->id;
		return $m;
	}

	function recursiveRender(){

		$transaction = $this->model;

		$cols= $this->add('Columns');
		$left=$cols->addColumn(6);
		$right=$cols->addColumn(6);

		$left->add('View')->set(array('Transaction Date : ' . $transaction['created_at'],'icon'=>'calendar'));
		$right->add('View')->set(array($transaction['transaction_type'],'icon'=>'check'));
		$grid=$this->add('Grid');
		$grid->setModel($transaction->ref('TransactionRow')->setOrder('amountDr desc, amountCr desc'),array('account','amountDr','amountCr'));

		$this->add('View')->set(array($transaction['Narration'],'icon'=>'pencil'));
		parent::recursiveRender();
	}

}