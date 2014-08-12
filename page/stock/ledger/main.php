<?php

class page_stock_ledger_main extends Page {
	function init(){
		parent::init();

		$tabs=$this->add('Tabs');
		$tab1=$tabs->addTabURL('stock_ledger_item','Item');
		$tab1=$tabs->addTabURL('stock_ledger_staff','Staff');
		$tab1=$tabs->addTabURL('stock_ledger_agent','Agent');
		$tab1=$tabs->addTabURL('stock_ledger_dealer','Dealer');
		$tab1=$tabs->addTabURL('stock_ledger_supplier','Supplier');

	}
}