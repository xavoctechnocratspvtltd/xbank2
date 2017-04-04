<?php
class page_reports_loan_bikelegal extends Page {
	public $title="Bike & Legal Report";
	
	function init(){
		parent::init();

		$tabs = $this->add('Tabs');
		$tabs->addTabURL('reports_loan_bikelegal_bikesinstock','Bike In Stock');
		$tabs->addTabURL('reports_loan_bikelegal_sellnoticedue','Sell Notice Due');
		$tabs->addTabURL('reports_loan_bikelegal_bikessra','Bike SRA');
		$tabs->addTabURL('reports_loan_bikelegal_finalrecoverynoticedue','Final Recovery Notice Due');
		$tabs->addTabURL('reports_loan_bikelegal_chequesubmitdue','Cheque Submit Due');
		$tabs->addTabURL('reports_loan_bikelegal_legalactionpendrive','Legal Action Pending');
		$tabs->addTabURL('reports_loan_bikelegal_chequereturnnoticedue','Cheque Return Notice Due');
		$tabs->addTabURL('reports_loan_bikelegal_legalcasesubmitdue','Legal Case Submit Due');
		$tabs->addTabURL('reports_loan_bikelegal_bikesnotsolddueto','Bike Not Sold Due To');
		$tabs->addTabURL('reports_loan_bikelegal_legalcasenotsubmitdueto','Legal Case Not Submit Due To');
		$tabs->addTabURL('reports_loan_bikelegal_bikescasedetailreport','Legal Case Detail Report');

	}
}