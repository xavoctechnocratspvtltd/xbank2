<?php

class page_reports_employee extends Page {
	public $title="Employee Reports";
	function init(){
		parent::init();
		
		$tabs=$this->add('Tabs')->addClass('noneprintalbe-ul');
		$tab1=$tabs->addTabURL('reports_conveyance','Conveyance Report');
		$tab1=$tabs->addTabURL('reports_cashfuel','Fuel Report');
		$tab1=$tabs->addTabURL('reports_moperformance','Mo Performance');
		$tab1=$tabs->addTabURL('reports_moassociation','MO Agent Association');
		$tab1=$tabs->addTabURL('reports_roperformance','RO Performance');
		$tab1=$tabs->addTabURL('reports_roaccountassociation','RO Account Association');
		$tab1=$tabs->addTabURL('reports_telecallerperformance','Telecaller Performance');
		$tab1=$tabs->addTabURL('reports_telecalleraccountassociation','Telecaller Account Association');
	}
}