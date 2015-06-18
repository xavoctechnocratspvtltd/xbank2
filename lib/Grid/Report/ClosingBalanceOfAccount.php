<?php

class Grid_Report_ClosingBalanceOfAccount extends Grid_AccountsBase{
	public $as_on_date;

	function setModel($model,$fields=null){
		parent::setModel($model,$fields);

		//Code
		$this->addColumn('closing_balance_of_account');
		$this->addFormatter('closing_balance_of_account','closing_balance_of_account');
		
		$this->addColumn('sm_account_no');
		$this->addFormatter('sm_account_no','sm_account_no');

		$this->addFormatter('member','Wrap');
		$this->addFormatter('PermanentAddress','Wrap');
		$this->addSno();
		$this->addPaginator(50);

	}

	function format_closing_balance_of_account($field){
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

	function format_sm_account_no($field){
		if(!$this->model['member_id'])
			$this->current_row[$field] = "Member Not Found";
			
		$member_model = $this->add('Model_Member')->load($this->model['member_id']);
		$number = $member_model->ref('Account')->addCondition('SchemeType','Default')->addCondition('scheme_name','Share Capital')->fieldQuery('AccountNumber');

		$this->current_row[$field] = $number; 
	}

	// function formatRow(){
	// 	parent::formatRow();
	// }
}