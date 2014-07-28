<?php

class page_agents extends Page{
	public $title ="Agent Management";

	function init(){
		parent::init();

		$crud = $this->add('CRUD');
		$crud->setModel('Agent');

	}

}