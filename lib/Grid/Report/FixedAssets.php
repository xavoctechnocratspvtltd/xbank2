<?php

class Grid_Report_FixedAssets extends Grid_AccountsBase{
	public $till_date;
	public $scheme_type;
	public $fixed_assets_type;
	public $current_financial_year; 

	function setModel($model,$fields=null){
		$this->current_financial_year = $this->api->getFinancialYear();

		$model->getElement('SchemeType')->caption('Under Head');
		parent::setModel($model,$fields);

		$this->addSno();
		$paginator = $this->addPaginator(50);
		$this->skip_var = $paginator->skip_var;

		$this->addColumn('opening_amount');
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
		$array = $this->model->getOpeningBalance($this->api->nextDate($this->till_date));
		$cr += $array['CR'];
		$dr += $array['DR'];

		$amount = $cr-$dr;
		$balance = $amount.' CR';
		if($amount < 0)
			$balance = abs($amount).' DR';
		
		//Opening Amount: Current Financial Year ka opening Amount no according to as on date
		
		// if($this->model['SchemeType'] == "Loan"){
			//OPENING BALANCE CALCULATION
			$start_date = $this->current_financial_year['start_date'];
			$opening_array = $this->model->getOpeningBalance($this->api->nextDate($start_date));

			$this->current_row['opening_amount'] = $opening_array['CR'];
			$this->current_row['closing_balance'] = $balance;
		// }else{
			// $this->current_row['closing_balance'] = $balance;
		// }
		
		// throw new \Exception($this->fixed_assets_type);
		
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