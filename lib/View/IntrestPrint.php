<?php

class View_IntrestPrint extends CompleteLister{
	function setModel($model){
		parent::setModel($model);

		// $this->api->stickyGET('account_id');
		// $account=$this->add('Model_Account_SM');
		// $account->load($_GET['account_id']);
		// // throw new Exception($this->model['Nominee'], 1);
		
		// $this->template->set('date','BCCS'.str_replace('SM', "",$account['AccountNumber']));
		// $this->template->set('from_date','BCCS'.str_replace('SM', "",$account['AccountNumber']));
		// $this->template->set('to_date','BCCS'.str_replace('SM', "",$account['AccountNumber']));
		// $this->template->set('customer_id','BCCS'.str_replace('SM', "",$account['AccountNumber']));
		// $this->template->set('name',$account->ref('member_id')->get('name'));
		// $this->template->set('address',$account->ref('member_id')->get('name'));
		// $this->template->set('phone_no',$account->ref('member_id')->get('name'));
		// $this->template->set('email_id',$account['AccountNumber']);
		// $this->template->set('total_intt_paid',$account['AccountNumber']);
		// $this->template->set('total_intt_collect',$account['AccountNumber']);
	}
	// function formatRow(){
		
	// 	// $this->current_row_html['account_no')=$this->model['account_no'];
	// 	// $this->current_row_html['curreny')=$this->model['account_no'];
	// 	// $this->current_row_html['intt_paid')=$this->model['account_no'];
	// 	// $this->current_row_html['intt_collect')=$this->model['account_no'];
		
	// 	parent::formatRow();
	// }

	function defaultTemplate(){
		return array('view/intrestcertificate');
	}
}