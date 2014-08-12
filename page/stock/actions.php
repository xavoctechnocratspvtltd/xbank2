<?php

class page_stock_actions extends Page {
	function init(){
		parent::init();

		$tabs=$this->add('Tabs');
		$tab1=$tabs->addTabURL('stock_actions_purchase','Purchase');
		$tab1=$tabs->addTabURL('stock_actions_return','Purchase Retrun');
		$tab1=$tabs->addTabURL('stock_actions_transfer','Transfer Stock');
		$tab1=$tabs->addTabURL('stock_actions_opening','Opening Stock');
		$tab1=$tabs->addTabURL('stock_actions_dead','Dead Stock');
		$tab1=$tabs->addTabURL('stock_actions_issue','Issue/Consume Stock');
		$tab1=$tabs->addTabURL('stock_actions_submit','Submit Stock');
		$tab1=$tabs->addTabURL('stock_actions_sold','Sold Dead Stock');

	}
}