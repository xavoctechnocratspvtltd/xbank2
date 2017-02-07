<?php

class View_FDPrint extends View{
	function setModel($model){
		parent::setModel($model);

		$this->api->stickyGET('account_id');
		$account=$this->add('Model_Account_FixedAndMis');
		$account->load($_GET['account_id']);
		// throw new Exception($this->model['Nominee'], 1);
		// $emp->convert_number_to_words($emp['net_payable'])
		// throw new Exception($account->convert_number_to_words($account['Amount']), 1);
		
		$this->template->set('branch',$account->ref('branch_id')->get('name'));
		$this->template->set('branch_1',$account->ref('branch_id')->get('name'));
		$this->template->set('date',date("d-M-Y",strtotime($account['created_at'])));
		$this->template->set('name',$account['ModeOfOperation']==="Joint"?$account->ref('member_id')->get('name')." (Joint) ":$account->ref('member_id')->get('name'));
		$this->template->set('name_1',$account->ref('member_id')->get('name'));
		$this->template->set('f_name',$account->ref('member_id')->get('FatherName'));
		$this->template->set('f_name_1',$account->ref('member_id')->get('FatherName'));
		$this->template->set('address',$account->ref('member_id')->get('CurrentAddress'));
		$this->template->set('address_1',$account->ref('member_id')->get('CurrentAddress'));
		$this->template->set('moblie_no',$account->ref('member_id')->get('PhoneNos'));
		$this->template->set('deposite_amount_word_1',$account->convert_number_to_words(round($account['Amount'])));
		$this->template->set('deposite_amount_word_2',$account->convert_number_to_words(round($account['Amount'])));
		$this->template->set('deposite_amount_1',$account['Amount']);
		$this->template->set('deposite_amount_2',$account['Amount']);
		
		if($account['ModeOfOperation']!="Joint"){
			$this->template->set('nominee_name',$account['Nominee']);
			$this->template->set('relation_with_nominee',$account['RelationWithNominee']);
			$this->template->set('nominee_age',$account['NomineeAge']);
			$this->template->set('nominee_dob',$account['MinorNomineeDOB']);
			
		}else{
			$this->template->tryDel('nominee_name');
			$this->template->tryDel('relation_with_nominee');
			$this->template->tryDel('nominee_age');
			$this->template->tryDel('nominee_dob');			
		}
		$this->template->set('special_candidate',"");
		$this->template->set('account_no',$account['AccountNumber']);
		$this->template->set('account_no_1',$account['AccountNumber']);
		$this->template->set('date_of_issue',date("d-M-Y",strtotime($account['created_at'])));
		$this->template->set('date_of_issue_1',date("d-M-Y",strtotime($account['created_at'])));
		$this->template->set('as_on_date',date("d-M-Y",strtotime($account['created_at'])));
		$this->template->set('as_on_date_1',date("d-M-Y",strtotime($account['created_at'])));
		$this->template->set('period',$account->ref('scheme_id')->get('MaturityPeriod'));
		$this->template->set('period_1',$account->ref('scheme_id')->get('MaturityPeriod'));
		$this->template->set('due_date',date("d-M-Y",strtotime($account['maturity_date'])));
		$this->template->set('due_date_1',date("d-M-Y",strtotime($account['maturity_date'])));
		$this->template->set('interest',$account->ref('scheme_id')->get('Interest')."%");
		$this->template->set('interest_1',$account->ref('scheme_id')->get('Interest')."%");
		$this->template->set('maturity_amount',$account['account_type']=='MIS'? $account['Amount'] : round($account->getAmountForInterest($account['maturity_date'])));
		$this->template->set('maturity_amount_1',$account['account_type']=='MIS'? $account['Amount'] :  round($account->getAmountForInterest($account['maturity_date'])));
		if($account['account_type']=='MIS'){
			$this->template->set('maturity_amount_word',$account->convert_number_to_words($account['Amount']?:round($account->getAmountForInterest($account['Amount']))));
		}else{
			$this->template->set('maturity_amount_word',$account->convert_number_to_words(round($account->getAmountForInterest($account['maturity_date']))));
		}
		
	}

	function defaultTemplate(){
		return array('view/fdprint');
	}
}