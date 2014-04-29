<?php

class page_branches extends Page {

	public $title = 'Branch Management';
		
	function init(){
		parent::init();

		$branch_crud = $this->add('CRUD');
		$branch_crud->setModel('Branch');

	}
}