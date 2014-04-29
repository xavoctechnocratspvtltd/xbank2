f<?php

class page_staffs extends Page {
	function init(){
		parent::init();

		$staff_crud = $this->add('CRUD');
		$staff_crud->setModel('Staff');

	}
}