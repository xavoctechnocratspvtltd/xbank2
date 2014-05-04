<?php

class page_accounts_SavingAndCurrent extends Page {
	function init(){
		parent::init();

		$crud=$this->add('CRUD');
		$crud->setModel('Account_SavingAndCurrent');

		
		if($crud->grid)
			$crud->grid->addPaginator(10);

	}
}