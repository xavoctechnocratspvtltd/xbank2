<?php

class page_reports_genral extends Page {
	public $title ="Genral Reports";
	function init(){
		parent::init();

		$tabs=$this->add('Tabs');
		$tab1=$tabs->addTabURL('reports_general_document','Genral Documents');
		$tab1=$tabs->addTabURL('reports_general_periodical','Periodical Documents');
	}
}