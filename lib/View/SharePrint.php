<?php

class View_SharePrint extends View{
	function setModel($model){
		parent::setModel($model);

		$this->api->stickyGET('account_id');
		$account=$this->add('Model_Account_SM');
		$account->load($_GET['account_id']);
		// throw new Exception($this->model['Nominee'], 1);
		
		$this->template->set('certificate_no','BCCS'.str_replace('SM', "",$account['AccountNumber']));
		$this->template->set('account_no',$account['AccountNumber']);
		$this->template->set('account_holder',$account->ref('member_id')->get('name'));
		$this->template->set('date',date('d-m-Y',strtotime($account['created_at'])));
		$this->template->set('no_of_share',$account['CurrentBalanceCr']/RATE_PER_SHARE);
		$this->template->set('no_of_shares',$account['CurrentBalanceCr']/RATE_PER_SHARE);
		
	}

	function defaultTemplate(){
		return array('view/sharecertificate');
	}
}