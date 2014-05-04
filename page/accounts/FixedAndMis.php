<?php

class page_Accounts_FixedAndMis extends Page {
	function init(){
		parent::init();

		$crud=$this->add('CRUD');
		$crud->setModel('Account_FixedAndMis');

		
		if($crud->grid)
			$crud->grid->addPaginator(10);

	}
}