<?php

class page_dashboard_weekly extends Page {
	function init(){
		parent::init();

		$from_date=date('Y-m-d', strtotime('Last Monday', strtotime($this->api->today)));
		$to_date=date('Y-m-d', strtotime('Next Sunday', strtotime($this->api->today)));

		$this->add('View_DuesReceiveList',array('from_date'=>$from_date,'to_date'=>$this->api->nextDate($to_date)));
		$this->add('View_DuesGiveList',array('from_date'=>$from_date,'to_date'=>$this->api->nextDate($to_date)));
		// $this->add('View_AccountOpenedOnDate',array('from_date'=>$from_date,'to_date'=>$this->api->nextDate($to_date)));
		
		$this->js('click',$this->js()->univ()->frameURL('Account Details',[$this->app->url('reports_loan_accountdetailed'),'accounts_no'=>$this->js()->_selectorThis()->data('id')]) )->_selector(' .acclink');
	}
}