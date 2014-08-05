<?php

class page_stock_main extends Page {
	function init(){
		parent::init();

		$tabs=$this->add('Tabs');
		$tab1=$tabs->addTabURL('stock_master','Master');
		$tab1=$tabs->addTabURL('stock_actions','Transaction Actions');
	}
}