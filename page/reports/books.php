<?php

class page_reports_books extends Page {
	public $title="Book Reports";
	function init(){
		parent::init();
		
		$tabs=$this->add('Tabs')->addClass('noneprintalbe-ul');
		$tab1=$tabs->addTabURL('reports_daybook','Day Book');
		$tab1=$tabs->addTabURL('reports_cashbook','Cash Book');
		// $tab1=$tabs->addTabURL('reports_BSAndPANL','Balance Sheet');
		// $tab1=$tabs->addTabURL('reports_pandl','P & L');
		$tab1=$tabs->addTabURL('reports_bs_balancesheet','Balance Sheet');
		$tab1=$tabs->addTabURL('reports_bs_easybsstudy','Easy Balancesheet Component Viewer');
		$tab1=$tabs->addTabURL('reports_pandl_pandl','P & L');
		$tab1=$tabs->addTabURL('utility_vouchersearch','Voucher Search');
		$tab1=$tabs->addTabURL('reports_transactioncount','Transaction Count');
	}
}