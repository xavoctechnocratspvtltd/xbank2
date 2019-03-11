<?php

class page_team extends Page {
	public $title='Team Management';

	function init(){
		parent::init();

		$crud = $this->add('CRUD');
		$crud->setModel('Team');
		$crud->add('Controller_Acl',['default_view'=>false]);

	}
}