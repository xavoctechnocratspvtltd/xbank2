<?php

class page_accounts_DDS extends Page {
	function init(){
		parent::init();

		$crud=$this->add('CRUD');
		$crud->setModel('Account_DDS');

	}
}