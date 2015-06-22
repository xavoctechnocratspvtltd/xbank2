<?php

class Grid_Report_CCOverDue extends Grid_AccountsBase{
	public $as_on_date;

	function setModel($model,$fields=null){
		parent::setModel($model,$fields);

		//Code
		// $this->addFormatter('member','Wrap');
		// $this->addFormatter('PermanentAddress','Wrap');
		$this->addSno();
		$this->addPaginator(5);

		$this->addColumn('cc_name');
		$this->addColumn('dr_balance');
		$this->addColumn('over_due_amount');
		$this->addColumn('over_due_amount_start_date');
	}

	// function formatRow(){
	// 	parent::formatRow();
	// }
}