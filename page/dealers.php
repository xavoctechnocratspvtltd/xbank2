<?php


class page_dealers extends Page {
	public $title ="Dealer management";

	function init(){
		parent::init();

		$crud = $this->add('CRUD');
		$dealer=$this->add('Model_Dealer');
		$dealer->setOrder('id','desc');
		$crud->setModel($dealer);
	}
}