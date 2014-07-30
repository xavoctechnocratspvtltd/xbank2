<?php


class page_dealers extends Page {
	public $title ="Dealer management";

	function init(){
		parent::init();

		$crud = $this->add('CRUD');
		$crud->setModel('Dealer');
	}
}