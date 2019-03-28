<?php

class page_supplier extends Page {
	public $title='Supplier Management';

	function init(){
		parent::init();

		$model = $this->add('Model_Supplier');
		$crud = $this->add('CRUD');
		$crud->setModel($model);
		$crud->add('Controller_Acl',['default_view'=>false]);
		
	}
}