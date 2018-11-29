<?php


class page_share_management extends Page {
	public $title = "Share Management";

	function init(){
		parent::init();

		$model = $this->add('Model_Share');
		$crud = $this->add('CRUD');

		$crud->setModel($model);

		$his_crud = $crud->addRef('ShareHistory');

	}
}