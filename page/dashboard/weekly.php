<?php

class page_dashboard_weekly extends Page {
	function init(){
		parent::init();

		$from_date=date('Y-m-d', strtotime('Last Monday', strtotime($this->api->today)));
		$to_date=date('Y-m-d', strtotime('Next Sunday', strtotime($this->api->today)));

		$this->add('View_DuesReceiveList',array('form_date'=>$from_date,'to_date'=>$this->api->nextDate($to_date)));
		$this->add('View_DuesGiveList',array('form_date'=>$from_date,'to_date'=>$this->api->nextDate($to_date)));
		$this->add('View_AccountOpenedOnDate',array('form_date'=>$from_date,'to_date'=>$this->api->nextDate($to_date)));
		

	}
}