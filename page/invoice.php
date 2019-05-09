<?php

class page_invoice extends Page {
	public $title='All Invoice';

	function init(){
		parent::init();

		$model = $this->add('Model_Transaction');
		$model->addCondition('is_sale_invoice',true);
		$model->getElement('created_at')->sortable(true);

		$crud = $this->add('CRUD',['allow_add'=>false,'allow_del'=>false]);
		$crud->setModel($model);

		$crud->addRef('TransactionRow',['label'=>'Detail']);
		$crud->grid->addPaginator(50);
	}
}