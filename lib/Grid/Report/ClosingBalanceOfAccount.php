<?php

class Grid_Report_ClosingBalanceOfAccount extends Grid_AccountsBase{
	public $as_on_date;

	function setModel($model,$fields=null){
		parent::setModel($model,$fields);

		//Code
		$this->addColumn('closing_balance_of_account');
		$this->addFormatter('closing_balance_of_account','closing_balance_of_account');
		
		// $this->addColumn('sm_account_no');
		// $this->addFormatter('sm_account_no','sm_account_no');

		// $this->model->getElement('name')->caption('Member');
		$this->addFormatter('PermanentAddress','Wrap');
		$this->addSno();
		$paginator = $this->addPaginator(500);
		$this->skip_var = $paginator->skip_var;

		$this->addQuickSearch(array('AccountNumber'));

		$this->removeColumn('sum');
		$this->removeColumn('member');
		$this->removeColumn('member_id');
		$this->removeColumn('SchemeType');
		$this->removeColumn('OpeningBalanceCr');
		$this->removeColumn('OpeningBalanceDr');
	}

	function format_closing_balance_of_account($field){
		$amount = $this->model['OpeningBalanceCr']-$this->model['OpeningBalanceDr'];
		$amount = $amount + $this->model['sum'];
		$balance = $amount.' CR';
		if($amount < 0)
			$balance = abs($amount).' DR';

		$this->current_row_html[$field] = $balance;
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