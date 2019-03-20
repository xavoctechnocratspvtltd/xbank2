<?php

class page_dealer_loandispatch extends page_dealer_dashboard{
	function init(){
		parent::init();

		$tab = $this->add('Tabs');
		$tab->addTabUrl($this->app->url('reports_loan_dispatch',['setdealer'=>$this->app->auth->model->id]),'Loan Dispatch');
		
	}
}