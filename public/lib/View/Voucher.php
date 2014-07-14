<?php

class View_Voucher extends View{
	public $transaction_id;

	function setModel($transaction){
		parent::setMode($transaction);
		$this->transaction_id = $transaction->id;
	}

	function recursiveRender(){
		$grid = $this->add('Grid');
		$grid->setModel($this->model->ref('TransactionRow'));
		parent::recursiveRender();
	}

}