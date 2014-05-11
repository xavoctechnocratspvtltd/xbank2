<?php

class page_documents extends Page {
	function init(){
		parent::init();

		$crud = $this->add('CRUD');
		$crud->setModel('Document');

	}
}