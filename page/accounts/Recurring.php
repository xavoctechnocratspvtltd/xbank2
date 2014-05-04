<?php

class page_accounts_Recurring extends Page {
	function init(){
		parent::init();

		$crud=$this->add('CRUD');
		$crud->setModel('Account_Recurring');

		
		if($crud->grid)
			$crud->grid->addPaginator(10);

	}
}