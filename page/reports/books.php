<?php

class page_reports_books extends Page {
	public $title="Book Reports";
	function init(){
		parent::init();
		
		$tabs=$this->add('Tabs')->addClass('noneprintalbe-ul');
		$tab1=$tabs->addTabURL('reports_daybook','Day Book');
		$tab1=$tabs->addTabURL('reports_cashbook','Cash Book');
		$tab1=$tabs->addTabURL('reports_BSAndPANL','Balance Sheet');
		$tab1=$tabs->addTabURL('reports_pandl','P & L');
	}
}