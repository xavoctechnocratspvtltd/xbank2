<?php

class page_reports_agent extends Page {
	public $title ="Agent Reports";
	function init(){
		parent::init();

		$tabs=$this->add('Tabs');
		$tab1=$tabs->addTabURL('reports_agent_tdsnewquarterly','New TDS Quarterly Report');
		$tab1=$tabs->addTabURL('reports_agent_tdsfromtable','New TDS Report');
		// $tab1=$tabs->addTabURL('reports_agent_tds','Agent TDS Report');
		$tab1=$tabs->addTabURL('reports_agent_status','Active / InActive Report');
		$tab1=$tabs->addTabURL('reports_agent_detail','Agent Detail Report');
		$tab1=$tabs->addTabURL('reports_agent_crpb','Agent CRPB Report');
		$tab1=$tabs->addTabURL('reports_agent_search','Agent Search');
		$tab1=$tabs->addTabURL('reports_agent_activeinactiveaccount','Agent\'s Accounts');
		$tab1=$tabs->addTabURL('reports_agent_topten','Top 10 Business');
		$tab1=$tabs->addTabURL('reports_agent_toptenmember','Top 10 Members');

	}
}