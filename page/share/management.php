<?php


class page_share_management extends Page {
	public $title = "Share Management";

	function init(){
		parent::init();

		$model = $this->add('Model_Share');
		$crud = $this->add('CRUD');

		$crud->setModel($model);

		$crud->grid->addPaginator(200);

		$his_crud = $crud->addRef('ShareHistory');

	}
}