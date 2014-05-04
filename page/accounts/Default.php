<?php

class page_accounts_Default extends Page {
	function init(){
		parent::init();


		$crud=$this->add('CRUD');
		$crud->setModel('Account_Default');

		
		if($crud->grid)
			$crud->grid->addPaginator(10);
	}
}