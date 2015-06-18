<?php

class page_reports_genral extends Page {
	public $title ="Genral Reports";
	function init(){
		parent::init();

		$tabs=$this->add('Tabs');
		$tab1=$tabs->addTabURL('reports_general_document','Genral Documents');
		$tab1=$tabs->addTabURL('reports_general_periodical','Periodical Accounts');
		$tab1=$tabs->addTabURL('reports_loan_accountdetailed','Account Detailed');
		// $tab1=$tabs->addTabURL('reports_general_defaulterlist','Defaulter List');
		$tab1=$tabs->addTabURL('reports_general_memberdepositeandloan','Member Deposite & Loan Report');
		$tab1=$tabs->addTabURL('reports_general_closingbalanceofaccount','Closing Balance of Account');
		$tab1=$tabs->addTabURL('reports_general_accountclose','Account Close Report');
		$tab1=$tabs->addTabURL('reports_general_fixedassets','Fixed Assets Report');
	}
}