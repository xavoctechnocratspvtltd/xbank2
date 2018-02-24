<?php

class Grid_Report_FixedAssets extends Grid_AccountsBase{
	public $from_date;
	public $to_date;
	public $scheme_type;
	public $fixed_assets_type;
	public $current_financial_year; 

	function setModel($model,$fields=null){
		$this->current_financial_year = $this->api->getFinancialYear();

		$model->getElement('scheme_name');
		parent::setModel($model,$fields);

		$this->addSno();
		$paginator = $this->addPaginator(50);
		$this->skip_var = $paginator->skip_var;

		$this->addColumn('purchase');
		$this->addColumn('opening_amount');
		$this->addColumn('depretiation_at');
		$this->addColumn('closing_balance');

	}

	function formatRow(){
		//Code
		// $account_model	= $this->add('Model_Account');
		// $account_model->addCondition('dealer_id',$this->model['dealer_id']);
		// $account_model->addCondition('SchemeType','Loan');
		
		$this->current_row['purchase'] = $this->model->ref('TransactionRow')->setOrder('created_at')->setLImit(1)->tryLoadAny()->get('amountDr');

		$cr = 0;
		$dr = 0;
		$openning = $this->model->getOpeningBalance($this->from_date);
		$cr = $openning['CR'];
		$dr = $openning['DR'];

		$amount = $cr-$dr;
		$balance = $amount.' CR';
		if($amount < 0)
			$balance = abs($amount).' DR';
	
		$this->current_row['opening_amount'] = $balance;

		$openning = $this->model->getOpeningBalance($this->api->nextDate($this->to_date));
		$cr = $openning['CR'];
		$dr = $openning['DR'];

		$amount = $cr-$dr;
		$balance = $amount.' CR';
		if($amount < 0)
			$balance = abs($amount).' DR';
		
		$this->current_row['closing_balance'] = $balance;

		//Depretitaion at: Plane&Machinary: 15%, Computer and Printer: 60% , Funrinture and fix:10%, Fixed Assets: 10%;
		switch ($this->fixed_assets_type) {
			case 'Fixed Assets':
				$this->current_row['depretiation_at'] = '10%';
				break;
			case 'plant & machionary':
				$this->current_row['depretiation_at'] = '15%';
				break;
			case 'furniture & fix':
				$this->current_row['depretiation_at'] = '10%';
				break;
			case 'computer & printer':
				$this->current_row['depretiation_at'] = '60%';
				break;
		}
				

		parent::formatRow();
	}
}