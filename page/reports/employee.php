<?php

class page_reports_employee extends Page {
	public $title="Employee Reports";
	function init(){
		parent::init();
		
		$tabs=$this->add('Tabs')->addClass('noneprintalbe-ul');
		$tab1=$tabs->addTabURL('reports_conveyance','Conveyance Report');
		$tab1=$tabs->addTabURL('reports_cashfuel','Fuel Report');
	}
}