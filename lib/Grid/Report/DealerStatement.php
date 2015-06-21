<?php

class Grid_Report_DealerStatement extends Grid_AccountsBase{
	public $from_date;
	public $to_date;
	public $account_type;
	public $account_status;

	function setModel($model,$fields=null){
		parent::setModel($model,$fields);

		$this->addColumn('loan_amount');
		$this->addColumn('net_amount');
		$this->addColumn('file_charge');
		$this->addColumn('bank_detail');

		$this->addFormatter('name','Wrap');
		$this->addSno();
		$this->addPaginator(5);

		$this->removeColumn('ActiveStatus');

	}

	function formatRow(){
		//Code
		// $account_model	= $this->add('Model_Account');
		// $account_model->addCondition('dealer_id',$this->model['dealer_id']);
		// $account_model->addCondition('SchemeType','Loan');
		
		$cr = 0;
		$dr = 0;
		$array = $this->model->getOpeningBalance($this->api->nextDate($this->to_date));
		$cr += $array['CR'];
		$dr += $array['DR'];

		$amount = $cr-$dr;
		$balance = $amount.' CR';
		if($amount < 0)
			$balance = abs($amount).' DR';
		
		if($this->model['SchemeType'] == "Loan"){
			$this->current_row['loan_amount'] = $balance;
		}else{
			$this->current_row['net_amount'] = $balance;
		}
		
		parent::formatRow();
	}
}