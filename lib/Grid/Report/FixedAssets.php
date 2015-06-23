<?php

class Grid_Report_FixedAssets extends Grid_AccountsBase{
	public $till_date;
	public $scheme_type;

	function setModel($model,$fields=null){
		parent::setModel($model,$fields);


		$this->addSno();
		$paginator = $this->addPaginator(50);
		$this->skip_var = $paginator->skip_var;

		$this->addColumn('purchase_date');
		$this->addColumn('opening_amount');
		$this->addColumn('under_head');
		$this->addColumn('depretiation_at');
		$this->addColumn('closing_balance');

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