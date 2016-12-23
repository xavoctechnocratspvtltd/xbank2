<?php

class page_reports_deleardsa extends Page {
	public $title ="Dealer And DSA Report";
	
	function init(){
		parent::init();

		$tabs=$this->add('Tabs');
		$tab1=$tabs->addTabURL('reports_dealerdsa_dealer','Dealer Report');
		$tab1=$tabs->addTabURL('reports_dealerdsa_dsa','DSA Report');

	}
}