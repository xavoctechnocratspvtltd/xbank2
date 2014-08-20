<?php

class page_dashboard_monthly extends Page {
	function init(){
		parent::init();

		$from_date=$this->api->monthFirstDate();
		$to_date=$this->api->monthLastDate();

		$this->add('View_DuesReceiveList',array('form_date'=>$from_date,'to_date'=>$this->api->nextDate($to_date)));
		$this->add('View_DuesGiveList',array('form_date'=>$from_date,'to_date'=>$this->api->nextDate($to_date)));
		$this->add('View_AccountOpenedOnDate',array('form_date'=>$from_date,'to_date'=>$this->api->nextDate($to_date)));
		

	}
}