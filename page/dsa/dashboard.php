<?php

class page_dsa_dashboard extends Page {
	
	function init(){
		parent::init();
		$this->title = $this->api->auth->model['name'];

		$tabs = $this->add('Tabs');
		
		// Dealers
		$tabs->addTabURl('dsa_dealers','Dealers');
		$tabs->addTabURl('dsa_emiduelist','EMI Due List');
		$tabs->addTabURl('dsa_emireceivedlist','EMI Received List');
		$tabs->addTabURl('dsa_rcduelist','R.C. Due List');
		$tabs->addTabURl('dsa_dealerstatement','Dealer Statements');
		$tabs->addTabURl('dsa_accountstatement','Account Statements');
		$tabs->addTabURl('dsa_dispatch','Loan Dispatch');
	}

}