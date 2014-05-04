<?php

class page_accounts_Loan extends Page {
	function init(){
		parent::init();

		$crud=$this->add('CRUD');
		$crud->setModel('Account_Loan');

		if($crud->grid)
			$crud->grid->addPaginator(10);

	}
}