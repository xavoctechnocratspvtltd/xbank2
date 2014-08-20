<?php

class page_dashboard_daily extends Page {
	function init(){
		parent::init();

		$this->add('View_DuesReceiveList',array('form_date'=>$this->api->today,'to_date'=>$this->api->nextDate($this->api->today)));
		$this->add('View_DuesGiveList',array('form_date'=>$this->api->today,'to_date'=>$this->api->nextDate($this->api->today)));
		$this->add('View_AccountOpenedOnDate',array('form_date'=>$this->api->today,'to_date'=>$this->api->nextDate($this->api->today)));
		
		// $this->add('View_AccountOpenedOnDate');

	}
}