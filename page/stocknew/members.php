<?php

class page_stocknew_members extends Page {
	public $title = "Members";

	function init(){
		parent::init();

		$crud = $this->add('CRUD');
		$crud->setModel('StockNew_Member');

		$crud->grid->addPaginator(50);

	}
}