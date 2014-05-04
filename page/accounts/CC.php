<?php

class page_accounts_CC extends Page {
	function init(){
		parent::init();

		$crud=$this->add('CRUD');
		$crud->setModel('Account_CC');

		
		if($crud->grid)
			$crud->grid->addPaginator(10);

	}
}