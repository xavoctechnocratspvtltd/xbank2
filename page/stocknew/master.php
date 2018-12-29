<?php

class page_stocknew_master extends Page {

	function init(){
		parent::init();

		$tabs=$this->add('Tabs');
		$tab1=$tabs->addTabURL('stocknew_itemcategory','Category & Items');
		$tab1=$tabs->addTabURL('stocknew_rowscontainers','Rows, Containers & Container Types');
		$tab1=$tabs->addTabURL('stocknew_members','Members');
		$tab1=$tabs->addTabURL('stocknew_transactiontemplates','Transaction Templates');
		$tab1=$tabs->addTabURL('stocknew_transactionedit','Edit Transactions');
		// $tab1=$tabs->addTabURL('stocknew_member','Member');
		// $tab1=$tabs->addTabURL('stocknew_containerrowitemqty','RowItemQty');
		
	}
}