<?php

class page_supplier extends Page {
	public $title='Supplier Management';

	function init(){
		parent::init();

		$this->add('Controller_Acl',['default_view'=>false]);
		
		$model = $this->add('Model_Supplier');
		$crud = $this->add('CRUD');
		if($crud->isEditing()){
			$crud->form->add('misc\Controller_FormAsterisk');
		}
		$crud->setModel($model);
		$crud->add('Controller_Acl',['default_view'=>false]);
		

	}
}