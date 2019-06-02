<?php

class page_reports_deposit extends Page {
	public $title ="Deposit Reports";
	function init(){
		parent::init();

		$tabs=$this->add('Tabs');
		$tab1=$tabs->addTab('-');
		// $tab1=$tabs->addTabURL('reports_deposit_fdProvision','FD Provision Report');
		$tab1=$tabs->addTabURL('reports_deposit_emiduelist','EMI Due List');
		$tab1=$tabs->addTabURL('reports_deposit_ddsduelist','DDS Due List');
		// $tab1=$tabs->addTabURL('reports_agent_tds','Agent Wise TDS Comm Report');
		// $tab1=$tabs->addTabURL('reports_deposit_fdinterestprovision','FD Interest Provision');
		$tab1=$tabs->addTabURL('reports_deposit_duestogive','Dues To Give');
		$tab1=$tabs->addTabURL('reports_deposit_duestoreceived','Dues To Received');
		$tab1=$tabs->addTabURL('reports_deposit_emireceivedlist','Premium Received List');
		$tab1=$tabs->addTabURL('reports_deposit_advancecheqpayment','Advance Cheque Payment');
		$tab1=$tabs->addTabURL('reports_deposit_matureaccountswithcrbal','Matured A/C With Cr Bal');
		// $tab1=$tabs->addTabURL('reports_deposit_tdsquaterly','TDS Quarterly');
		// $tab1=$tabs->addTabURL('reports_deposit_commission','Branch Commission Report');
		$tab1=$tabs->addTabURL('reports_deposit_fundsflow','Funds Flow Report');
		$tab1=$tabs->addTabURL('reports_deposit_comparativebusiness','Comparative Business Report');
		$tab1=$tabs->addTabURL('reports_deposit_nonactiverdaccountholders','Non Active RD Holders');
	}
}