<?php

class page_reports_recovery extends Page {
	public $title ="Recovery Reports";
	function init(){
		parent::init();

		$tabs=$this->add('Tabs');
		$tab1=$tabs->addTabURL('reports_loan_emiduelist','EMI Due List');
		$tab1=$tabs->addTabURL('reports_loan_emireceivedlist','EMI Rceceived List');
		$tab1=$tabs->addTabURL('reports_loan_penaltyandotherreceivedlist','Penalty & Other Received List');
		$tab1=$tabs->addTabURL('reports_loan_gstreceivedlist','GST Received List');
		$tab1=$tabs->addTabURL('reports_loan_duestoreceive','Dues To Receive List');
		// $tab1=$tabs->addTabURL('reports_loan_insuranceduelist','Insurance Due List');
		// $tab1=$tabs->addTabURL('reports_loan_dispatch','Loan Dispatch');
		// $tab1=$tabs->addTabURL('reports_loan_approval','Approval Letter');
		// $tab1=$tabs->addTabURL('reports_loan_rcduelist','R.C. Due List');
		// $tab1=$tabs->addTabURL('reports_loan_dealerstatement','Dealer Statement');
		// $tab1=$tabs->addTabURL('reports_loan_noc','NOC');
		// $tab1=$tabs->addTabURL('reports_loan_informationletter','Information Letter');
		$tab1=$tabs->addTabURL('reports_loan_forclose','For Close Report');
		// $tab1=$tabs->addTabURL('reports_loan_overdue','CC Over Due Report');
		// $tab1=$tabs->addTabURL('reports_loan_documentrenew','CC Document Renew Report');
		$tab1=$tabs->addTabURL('reports_loan_newNPAaccount','New NPA Account List');
		$tab1=$tabs->addTabURL('reports_loan_dealerwise','Dealer Wise Recovery');
		$tab1=$tabs->addTabURL('reports_loan_dealerwisereceived','Dealer Wise Received');
		$tab1=$tabs->addTabURL('roaccountassociation','Ro Account Association');
		$tab1=$tabs->addTabURL('telecalleraccountassociation','TeleCaller Account Association');
		$tab1=$tabs->addTabURL('reports_recoveryandlegalcharges','Recovery & Legal Charges Report');
		$tab1=$tabs->addTabURL('reports_societyandlegalnotice','Society & Legal Notice');
			

	}
}