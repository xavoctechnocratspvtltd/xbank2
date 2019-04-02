<?php

class page_memorandum_entry extends Page{

	function init(){
		parent::init();

		$model = $this->add('Model_Memorandum_Transaction');

		$crud = $this->add('CRUD');
		$crud->setModel($model);

		$crud->addRef('Memorandum_TransactionRow');
	}
}