<?php

class page_documents extends Page {
	public $title='Documents Management';
	
	function init(){
		parent::init();

		$crud = $this->add('CRUD');
		$crud->setModel('DocumentAll');
		$crud->add('Controller_Acl',['default_view'=>false]);
	}
}