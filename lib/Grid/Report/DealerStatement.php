<?php

class Grid_Report_DealerStatement extends Grid_AccountsBase{
	public $from_date;
	public $to_date;
	public $account_type;
	public $account_status;

	public $loan_amount_sum=0;
	public $net_amount_sum=0;
	public $file_charge_sum=0;

	public $formating_totals=false;

	function setModel($model,$fields=null){
		parent::setModel($model,$fields);

		$this->addColumn('calcColumns','loan_amount');
		$this->addColumn('calcColumns','net_amount');
		$this->addColumn('calcColumns','file_charge');

		$this->addFormatter('name','Wrap');
		$this->addSno();
		$paginator = $this->addPaginator(500);
		$this->skip_var = $paginator->skip_var;

		$this->removeColumn('ActiveStatus');
		$this->removeColumn('dealer_id');
		
		$order=$this->addOrder();
		$order->move('file_charge', 'after','loan_amount')->now();

	}

	function format_calcColumns($field){

	}

	function format_totals_calcColumns($field){
		$this->formating_totals=true;
		$this->current_row[$field] = $this->{$field.'_sum'};
	}
	

	function formatRow(){
		// First transaction of this account
		$transactions = $this->add('Model_Transaction')->addCondition('reference_id',$this->model->id)->setLimit(1)->setOrder('id','asc')->tryLoadAny();
		$transactions_row = $this->add('Model_TransactionRow')->addCondition('transaction_id',$transactions->id)->setOrder('id','asc');
		$i = 1;
		foreach ($transactions_row as $tr) {
			if($i == 1) // first row of this transaction
				$this->current_row['loan_amount'] = $tr['amountDr'];
			if($i == 2)
				$this->current_row['file_charge'] = $tr['amountCr'];
			if($i == 3)
				$this->current_row['net_amount'] = $tr['amountCr'];
			$i++;
		}

		if(!$this->current_row['net_amount']){
			// Looks like there was no file charge and may be even 3rd transaction row does not exists
			$this->current_row['net_amount'] = $this->current_row['file_charge'];
			$this->current_row['file_charge'] = 0;
		}

		if($this->current_row['AccountNumber']){
			$this->loan_amount_sum += $this->current_row['loan_amount'];
			$this->net_amount_sum += $this->current_row['net_amount'];
			$this->file_charge_sum += $this->current_row['file_charge'];
		}

		parent::formatRow();
	}
}