<?php

class page_dsa extends Page {
	public $title ='DSA Management';
	
	function init(){
		parent::init();

		$dsa_model = $this->add('Model_DSA');
		$crud = $this->add('CRUD');
		$crud->setModel($dsa_model);
	}
}