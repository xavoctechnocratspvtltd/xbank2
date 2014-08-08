<?php

class page_stock_reports_main extends Page {
	function init(){
		parent::init();

		$tabs=$this->add('Tabs');
		$tab1=$tabs->addTabURL('stock_reports_dead','Dead Stock');

	}
}