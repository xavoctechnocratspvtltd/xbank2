<?php

class page_reports_bs_schemegrouptoaccounts extends Page{
	function init(){
		parent::init();

		$from_date = $this->api->stickyGET('from_date');
		$to_date = $this->api->stickyGET('to_date');
		$branch_id = $this->api->stickyGET('branch_id');
		$scheme_group = $this->api->stickyGET('scheme_group');

		echo $from_date.'<br>';
		echo $to_date.'<br>';
		echo $branch_id.'<br>';
		echo $scheme_group.'<br>';		
	}
}