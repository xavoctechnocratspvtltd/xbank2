<?php

class page_stock_reports_main extends Page {
	function init(){
		parent::init();

		$tabs=$this->add('Tabs');

		$tab1=$tabs->addTabURL('stock_reports_stock','Stock Report');
		$tab1=$tabs->addTabURL('stock_reports_staff','Staff Report');
		$tab1=$tabs->addTabURL('stock_reports_agent','Agent Report');
		$tab1=$tabs->addTabURL('stock_reports_dealer','Dealer Report');
		$tab1=$tabs->addTabURL('stock_reports_supplier','Supplier Report');
		// $tab1=$tabs->addTabURL('stock_reports_purchase','Purchase Report');
		// // $tab1=$tabs->addTabURL('stock_reports_stock','Stock Report');
		// $tab1=$tabs->addTabURL('stock_reports_item','Item Report');
		// $tab1=$tabs->addTabURL('stock_reports_genral','Genral Report');

	}
}