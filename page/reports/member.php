<?php

class page_reports_member extends Page {
	public $title ="Member Reports";
	function init(){
		parent::init();

		$tabs=$this->add('Tabs');
		$tab1=$tabs->addTabURL('reports_member_member','Member Reports');
		$tab1=$tabs->addTabURL('reports_member_loaninsurance','Loan Insurance Reports');
		$tab1=$tabs->addTabURL('reports_member_depositinsurance','Deposite Insurance');
		$tab1=$tabs->addTabURL('reports_member_defaulter','Defaulter List');
	}
}