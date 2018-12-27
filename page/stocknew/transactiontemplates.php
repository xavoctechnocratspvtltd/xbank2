<?php


class page_stocknew_transactiontemplates extends Page {
	public $title = "Transaction Templates";

	function init(){
		parent::init();

		$model = $this->add('Model_StockNew_TransactionTemplate');

		$crud = $this->add('CRUD');
		$crud->setModel($model);
	}
}