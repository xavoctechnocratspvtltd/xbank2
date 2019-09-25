<?php

class page_reports_member extends Page {
	public $title ="Member Reports";
	function init(){
		parent::init();

		$tabs=$this->add('Tabs');
		$tab1=$tabs->addTabURL('reports_member_member','Member Reports');
		// $tab1=$tabs->addTabURL('reports_member_loaninsurance','Loan Member Insurance Report');
		$tab1=$tabs->addTabURL('reports_member_loaninsurancenew','New Loan Member Insurance Report');
		$tab1=$tabs->addTabURL('reports_member_depositinsurance','Deposit  Member Insurance Report');
		$tab1=$tabs->addTabURL('reports_member_defaulter','Defaulter List');
		$tab1=$tabs->addTabURL('utility_activemember','Active Memebr');
		$tab1=$tabs->addTabURL('reports_member_loandetails','Member Loan Details');
		$tab1=$tabs->addTabURL('reports_member_smaudit','Member SM Audit');
		$tab1=$tabs->addTabURL('reports_member_nonactiveaccount','SM Member WithOut Active Loan/RD/DDS Accounts');
	}
}