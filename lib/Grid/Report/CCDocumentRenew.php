<?php

class Grid_Report_CCDocumentRenew extends Grid_AccountsBase{
	public $as_on_date;

	function setModel($model,$fields=null){
		parent::setModel($model,$fields);

		//Code
		// $this->addFormatter('member','Wrap');
		// $this->addFormatter('PermanentAddress','Wrap');
		$this->addSno();
		$paginator = $this->addPaginator(50);
		$this->skip_var = $paginator->skip_var;

		$this->addColumn('cc_name');
		$this->addColumn('current_balance');
		$this->addColumn('last_document_renew_date');

		$this->addFormatter('name','Wrap');
		$this->addFormatter('CurrentAddress','Wrap');
	}

	function formatRow(){
		
		$cc_account_model	= $this->add('Model_Account_CC')->load($this->model->id);
		$array = $cc_account_model->getOpeningBalance($this->api->nextDate($this->as_on_date));
		$cr = $array['CR'];
		$dr = $array['DR'];

		$amount = $cr-$dr;
		$balance = $amount.' CR';
		if($amount < 0)
			$balance = abs($amount).' DR';

		//CC Account Current Balance 
		$this->current_row_html['current_balance'] = $balance;

		parent::formatRow();
	}
}