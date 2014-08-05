<?php

class page_balancesheet extends Page {
	public $title='Top Heads of balance Sheet';

	function init(){
		parent::init();

		$crud= $this->add('CRUD');
		$crud->setModel('BalanceSheet');
		
	}
}