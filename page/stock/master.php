<?php

class page_stock_master extends Page {

	function init(){
		parent::init();

		$tabs=$this->add('Tabs');
		$tab1=$tabs->addTabURL('stock_container','Container');
		$tab1=$tabs->addTabURL('stock_row','Rows');
		$tab1=$tabs->addTabURL('stock_category','Category');
		$tab1=$tabs->addTabURL('stock_item','Item');
		$tab1=$tabs->addTabURL('stock_member','Member');
		$tab1=$tabs->addTabURL('stock_containerrowitemqty','RowItemQty');
		
	}
}