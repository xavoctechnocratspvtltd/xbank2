<?php


class page_dealers extends Page {
	public $title ="Dealer management";

	function init(){
		parent::init();
		$this->add('Controller_Acl');
		
		$crud = $this->add('CRUD');
		$dealer=$this->add('Model_Dealer');
		$dealer->setOrder('id','desc');
		$crud->setModel($dealer);
		$crud->add('Controller_Acl');
	}
}