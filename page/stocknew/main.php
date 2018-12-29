<?php

class page_stocknew_main extends Page {
	public $title = "New Stock System";

	function init(){
		parent::init();

		$tabs=$this->add('Tabs');
		$tab1=$tabs->addTabURL('stocknew_reports','Reports');
		$tab1=$tabs->addTabURL('stocknew_ledgers','Ledgers');
		$tab1=$tabs->addTabURL('stocknew_master','Master');
		$tab1=$tabs->addTabURL('stocknew_transactions','Transactions');
		// $tab1=$tabs->addTabURL('stocknew_reports_main','Reports');
	}
}