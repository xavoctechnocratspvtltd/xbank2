<?php

class View_FDPrint extends View{
	function setModel($model){
		parent::setModel($model);

		$this->api->stickyGET('account_id');
		$account=$this->add('Model_Account_FixedAndMis');
		$account->load($_GET['account_id']);
		// throw new Exception($this->model['Nominee'], 1);
		
		$this->template->set('name',$account->ref('member_id')->get('name'));
		$this->template->set('f_name',$account->ref('member_id')->get('FatherName'));
		$this->template->set('address',$account->ref('member_id')->get('CurrentAddress'));
		$this->template->set('deposite_amount',$account['Amount']);
		$this->template->set('nominee_name',$account['Nominee']);
		$this->template->set('relation_with_nominee',$account['RelationWithNominee']);
		$this->template->set('nominee_age',$account['NomineeAge']);
		$this->template->set('nominee_dob',date('d-m-Y',strtotime($account['MinorNomineeDOB'])));
		$this->template->set('special_candidate',"");
		$this->template->set('account_no',$account['AccountNumber']);
		$this->template->set('date_of_issue',date("d-M-Y",strtotime($account['created_at'])));
		$this->template->set('as_on_date',date("d-M-Y",strtotime($account['created_at'])));
		$this->template->set('period',$account->ref('scheme_id')->get('MaturityPeriod'));
		$this->template->set('due_date',date("d-M-Y",strtotime($account['maturity_date'])));
		$this->template->set('interest',$account->ref('scheme_id')->get('Interest')."%");
		$this->template->set('maturity_amount',$account->getAmountForInterest($account['maturity_date']));
		
	}

	function defaultTemplate(){
		return array('view/fdprint');
	}
}