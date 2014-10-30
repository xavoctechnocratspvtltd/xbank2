<?php

class page_stock_main extends Page {

	public $title= 'Stock Manager';
	
	function init(){
		parent::init();

		$tabs=$this->add('Tabs');
		$tab1=$tabs->addTabURL('stock_master','Master');
		$tab1=$tabs->addTabURL('stock_actions','Transaction Actions');
		$tab1=$tabs->addTabURL('stock_ledger_main','Ledgers');
		$tab1=$tabs->addTabURL('stock_reports_main','Reports');
	}
}