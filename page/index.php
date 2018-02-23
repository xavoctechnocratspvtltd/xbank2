<?php

class page_index extends xPage{
	public $title ="Dashboard";
	
	function init(){
		parent::init();
		
		// $this->add('View_DuesReceiveList');
		// $this->add('View_DuesGiveList');
		// $this->add('View_AccountOpenedOnDate');

		$tabs=$this->add('Tabs');
		$tab1=$tabs->addTabURL('dashboard_main','.');
		$tab2=$tabs->addTabURL('dashboard_daily','Daily Dues');
		$tab2=$tabs->addTabURL('dashboard_weekly','Weekly Dues');
		$tab2=$tabs->addTabURL('dashboard_monthly','Monthly Dues');
		$tab2=$tabs->addTabURL('dashboard_opened','Accounts Opened Today');
		$tab2=$tabs->addTabURL('dashboard_cash','Cash / Bank Reports');
		$tab2=$tabs->addTabURL('dashboard_insurance','Insurance Due List');
		$tab2=$tabs->addTabURL('dashboard_scheme','Scheme');
	}
}