<?php

class page_mos extends Page {
	public $title='Marketing manager (MO) Management';

	function init(){
		parent::init();

		$crud = $this->add('CRUD');
		$crud->setModel('Mo');


	}
}