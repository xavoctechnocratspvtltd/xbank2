<?php

class page_reports_loan extends Page {
	public $title ="Loan Reports";
	function init(){
		parent::init();

		$tabs=$this->add('Tabs');
		// $tab1=$tabs->addTabURL('reports_loan_emiduelist','EMI Due List');
		// $tab1=$tabs->addTabURL('reports_loan_emireceivedlist','EMI Rceceived List');
		$tab1=$tabs->addTabURL('reports_loan_insuranceduelist','Insurance Due List');
		$tab1=$tabs->addTabURL('reports_loan_dispatch','Loan Dispatch');
		$tab1=$tabs->addTabURL('reports_loan_approval','Approval Letter');
		$tab1=$tabs->addTabURL('reports_loan_rcduelist','R.C. Due List');
		$tab1=$tabs->addTabURL('reports_loan_dealerstatement','Dealer Statement');
		$tab1=$tabs->addTabURL('reports_loan_dealeraccountstatement','Dealer Account Statement');
		$tab1=$tabs->addTabURL('reports_loan_noc','NOC');
		// $tab1=$tabs->addTabURL('reports_loan_informationletter','Information Letter');
		// $tab1=$tabs->addTabURL('reports_loan_forclose','For Close Report');
		// $tab1=$tabs->addTabURL('reports_loan_overdue','CC Over Due Report');
		// $tab1=$tabs->addTabURL('reports_loan_documentrenew','CC Document Renew Report');
		// $tab1=$tabs->addTabURL('reports_loan_newNPAaccount','New NPA Account List');
		// $tab1=$tabs->addTabURL('reports_loan_dealerwise','Dealer Wise Recovery');
		$tab1=$tabs->addTabURL('reports_loan_dealerwiseloanreport','Dealer Wise Loan Report');
		$tab1=$tabs->addTabURL('noclog_report','NOC Report');
		$tab1=$tabs->addTabURL('reports_loan_dla','D.L.A');

	}
}