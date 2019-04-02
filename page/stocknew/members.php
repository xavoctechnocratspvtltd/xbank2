<?php

class page_stocknew_members extends Page {
	public $title = "Members";

	function init(){
		parent::init();

		$this->add('Controller_Acl',['default_view'=>false]);
		$crud = $this->add('CRUD');
		$crud->setModel('StockNew_Member');

		$crud->grid->addPaginator(50);

	}
}