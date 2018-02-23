<?php

class page_dashboard_daily extends Page {
	function init(){
		parent::init();

		$this->add('View_DuesReceiveList',array('form_date'=>$this->api->today,'to_date'=>$this->api->nextDate($this->api->today)));
		$this->add('View_DuesGiveList',array('from_date'=>$this->api->today,'to_date'=>$this->api->nextDate($this->api->today)));
		// $this->add('View_AccountOpenedOnDate',array('form_date'=>$this->api->today,'to_date'=>$this->api->nextDate($this->api->today)));
		// $this->add('View_AccountOpenedOnDate');
		$this->js('click',$this->js()->univ()->frameURL('Account Details',[$this->app->url('reports_loan_accountdetailed'),'accounts_no'=>$this->js()->_selectorThis()->data('id')]) )->_selector(' .acclink');
	}
}