<?php


class page_reports_bs_balancesheet extends Page {
	
	function init(){
		parent::init();

		$grid = $this->add('Grid');
		$grid->setModel('BS_BalanceSheet');

	}
}