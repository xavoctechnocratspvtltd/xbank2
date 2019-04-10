<?php


class page_stocknew_transactiontemplates extends Page {
	public $title = "Transaction Templates";

	function init(){
		parent::init();

		$this->add('Controller_Acl',['default_view'=>false]);
		
		$model = $this->add('Model_StockNew_TransactionTemplate');

		$crud = $this->add('CRUD');
		$crud->setModel($model);

		$crud->add('Controller_Acl',['default_view'=>false]);
	}
}