<?php

class page_stocknew_members extends Page {
	public $title = "Members";

	function init(){
		parent::init();

		$this->add('Controller_Acl',['default_view'=>false]);
		$crud = $this->add('CRUD');
		$crud->setModel('StockNew_Member');

		$crud->grid->addPaginator(500);
		$crud->grid->addQuickSearch(['name','ph_no']);
		$crud->add('Controller_Acl',['default_view'=>false]);
	}
}