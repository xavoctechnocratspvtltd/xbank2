<?php

class page_voucher_print extends Page {
	function init(){
		parent::init();

		$this->api->stickyGET('transaction_id');

		$transaction = $this->add('Model_Transaction');
		$transaction->load($_GET['transaction_id']);

		$cols= $this->add('Columns');
		$left=$cols->addColumn(6);
		$right=$cols->addColumn(6);

		$left->add('View')->set(array('Transaction Date : ' . $transaction['created_at'],'icon'=>'calendar'));
		$right->add('View')->set(array($transaction['transaction_type'],'icon'=>'check'));
		$grid=$this->add('Grid');
		$grid->setModel($transaction->ref('TransactionRow')->setOrder('amountDr desc, amountCr desc'),array('account','amountDr','amountCr'));

		$this->add('View')->set(array($transaction['Narration'],'icon'=>'pencil'));
		$this->add('View')->set(array($transaction['reference'],'icon'=>'pencil'));

	}
}