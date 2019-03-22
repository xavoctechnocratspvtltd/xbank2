<?php

class page_telecaller_historyedit extends Page {
	public $title = "TeleCaller History Edit";

	function init(){
		parent::init();

		$this->add('Controller_Acl',['default_view'=>false]);

		$m= $this->add('Model_TeleCallerAccountHistory');

		$crud= $this->add('CRUD');
		$crud->setModel($m);
		$crud->add('Controller_Acl',['default_view'=>false]);
		$crud->grid->addPaginator(100);
	}
}