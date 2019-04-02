<?php

class page_stocknew_transactionedit extends Page {
	public $title = "Top level transaction Edit";

	function init(){
		parent::init();

		$this->add('Controller_Acl',['default_view'=>false]);
		
		$model = $this->add('Model_StockNew_Transaction');
		$crud  = $this->add('CRUD');
		$crud->setModel($model);

		$crud->grid->addPaginator(100);
	}
}