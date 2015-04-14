<?php

class page_stock_actions extends Page {
	function init(){
		parent::init();

		$tabs=$this->add('Tabs');
		$tab1=$tabs->addTabURL('stock_actions_opening','Opening');
		$tab1=$tabs->addTabURL('stock_actions_purchase','Purchase');
		$tab1=$tabs->addTabURL('stock_actions_return','Purchase Retrun');
		$tab1=$tabs->addTabURL('stock_actions_move','Move');
		$tab1=$tabs->addTabURL('stock_actions_transfer','Transfer');
		$tab1=$tabs->addTabURL('stock_actions_issue','Issue');
		$tab1=$tabs->addTabURL('stock_actions_consume','Consume');
		$tab1=$tabs->addTabURL('stock_actions_submit','Submit');
		$tab1=$tabs->addTabURL('stock_actions_usedsubmit','Used Submit');
		$tab1=$tabs->addTabURL('stock_actions_dead','Dead');
		$tab1=$tabs->addTabURL('stock_actions_deadsold','Dead Sold');

	}
}