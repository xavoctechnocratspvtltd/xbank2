<?php

class Grid_Report_MemberDepositeAndLoan extends Grid_AccountsBase{
	public $as_on_date;

	function setModel($model,$fields=null){
		parent::setModel($model,$fields);

		//Code
		$this->addColumn('share_account_amount');
		$this->addFormatter('member_name','Wrap');

		$this->addColumn('deposite_amount');
		$this->addFormatter('deposite_amount','deposite_amount');
		
		$this->addColumn('loan_amount');
		// $this->addFormatter('loan_amount','loan_amount');
		
		$this->addColumn('purpose_for_loan');
		
		$this->addSno();
		$paginator = $this->addPaginator(2000);
		$this->skip_var = $paginator->skip_var;

		$this->addOrder()->move('share_account_amount','after','sm_no')->now();
	}

	function format_deposite_amount($field){
		//rd,dds,fd,mis Account
		$account_model	= $this->add('Model_Account');
		$account_model->addCondition('member_id',$this->model->id);
		$account_model->addCondition('SchemeType',array('DDS',ACCOUNT_TYPE_FIXED,'Recurring'));
		$cr = 0;
		$dr = 0;
		
		foreach ($account_model as $account) {
			$array = $account->getOpeningBalance($this->api->nextDate($this->as_on_date));
			$cr += $array['CR'];
			$dr += $array['DR'];
		}	

		$amount = $cr-$dr;
		$balance = $amount.' CR';
		if($amount < 0)
			$balance = abs($amount).' DR';

		$this->current_row[$field] = $balance;
	}

	function formatRow(){
		//Code
		$account_model	= $this->add('Model_Account');
		$account_model->addCondition('member_id',$this->model->id);
		$account_model->addCondition('SchemeType','Loan');
		$cr = 0;
		$dr = 0;
		$purpose_for_loan_array = array();
		foreach ($account_model as $account) {
			$array = $account->getOpeningBalance($this->api->nextDate($this->as_on_date));
			$cr += $array['CR'];
			$dr += $array['DR'];

			if(!in_array($account['account_type'], $purpose_for_loan_array))
				$purpose_for_loan_array[] = $account['account_type'];
		}	

		$amount = $cr-$dr;
		$balance = $amount.' CR';
		if($amount < 0)
			$balance = abs($amount).' DR';

		//Loan Amount 
		$this->current_row['loan_amount'] = $balance;
		
		$purpose_for_loan=""; 
		foreach ($purpose_for_loan_array as $key => $value) {
			$purpose_for_loan .='<br>'.$value;
		}
		$this->current_row_html['purpose_for_loan'] = $purpose_for_loan;


		$account_model	= $this->add('Model_Account');
		$account_model->addCondition('member_id',$this->model->id);
		$account_model->addCondition('SchemeType','Default')->addCondition('scheme_name','Share Capital');
		$account_model->tryLoadAny();
		$this->current_row_html['share_account_amount'] = $account_model['CurrentBalanceCr'] - $account_model['CurrentBalanceDr'];


		parent::formatRow();
	}	
}