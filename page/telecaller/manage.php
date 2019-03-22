<?php


class page_telecaller_manage extends Page {
	public $title='Telecaller Management';

	function init(){
		parent::init();

		$this->add('Controller_Acl',['default_view'=>false]);

		$m = $this->add('Model_TeleCaller');

		$crud = $this->add('CRUD');
		$crud->setModel($m);

		$crud->grid->addPaginator(100);
		$crud->add('Controller_Acl',['default_view'=>false]);

	}
}