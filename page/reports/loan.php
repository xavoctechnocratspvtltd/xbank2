<?php

class page_reports_loan extends Page {
	public $title ="Loan Reports";
	function init(){
		parent::init();

		$tabs=$this->add('Tabs');
		$tab1=$tabs->addTabURL('reports_loan_emiduelist','EMU Due List');
		$tab1=$tabs->addTabURL('reports_loan_emireceivedlist','EMI Rceceived List');
		$tab1=$tabs->addTabURL('reports_loan_insuranceduelist','Insurance Due List');
		$tab1=$tabs->addTabURL('reports_loan_dispatch','Loan Dispatch');
		$tab1=$tabs->addTabURL('reports_loan_accountdetailed','Account Detailed');
	}
}