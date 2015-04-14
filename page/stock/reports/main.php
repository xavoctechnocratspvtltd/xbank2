<?php

class page_stock_reports_main extends Page {
	function init(){
		parent::init();

		$tabs=$this->add('Tabs');

		$tabs->addTabURL('stock_reports_staff','Staff Report');
		$tabs->addTabURL('stock_reports_agent','Agent Report');
		$tabs->addTabURL('stock_reports_dealer','Dealer Report');
		$tabs->addTabURL('stock_reports_supplier','Supplier Report');
		$tabs->addTabURL('stock_reports_itemwisestaff','Item wise Staff Report');
		$tabs->addTabURL('stock_reports_itemtransaction','Item Transaction');
		$tab1=$tabs->addTabURL('stock_reports_item','Item Report');
		$tabs->addTabURL('stock_reports_stock','Stock Report');
		// $tab1=$tabs->addTabURL('stock_reports_genral','Genral Report');

	}
}