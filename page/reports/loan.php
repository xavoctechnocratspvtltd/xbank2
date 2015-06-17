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
		$tab1=$tabs->addTabURL('reports_loan_approval','Approval Letter');
		$tab1=$tabs->addTabURL('reports_loan_rcduelist','R.C. Due List');
		$tab1=$tabs->addTabURL('reports_loan_dealerstatement','Dealer Statement');
		$tab1=$tabs->addTabURL('reports_loan_noc','NOC');
		$tab1=$tabs->addTabURL('reports_loan_forclose','For Close Report');
		$tab1=$tabs->addTabURL('reports_loan_overdue','CC Over Due Report');
		$tab1=$tabs->addTabURL('reports_loan_documentrenew','CC Document Renew Report');
			

	}
}