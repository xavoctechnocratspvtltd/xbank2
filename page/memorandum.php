<?php

class page_memorandum extends Page {
	public $title='All Memorandum';

	function init(){
		parent::init();

		$model = $this->add('Model_Memorandum_Transaction');
		$model->getElement('created_at')->sortable(true);
		$crud = $this->add('CRUD');
		$crud->setModel($model);
		$crud->addRef('Memorandum_TransactionRow',['label'=>'Detail']);

		$crud->grid->addPaginator(50);
	}
}