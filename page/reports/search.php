<?php

class page_reports_search extends Page {
	public $title ="Search";
	function init(){
		parent::init();

		$tabs=$this->add('Tabs');
		$tab1=$tabs->addTabURL('reports_search_member','Search Member');
		$tab1=$tabs->addTabURL('reports_search_account','Search Account');
	}
}