<?php

class Grid_Report_AccountClose extends Grid_AccountsBase{
	public $from_date;
	public $to_date;

	function setModel($model,$fields=null){
		parent::setModel($model,$fields);

		//Code
		// $this->addFormatter('member','Wrap');
		// $this->addFormatter('PermanentAddress','Wrap');
		$this->addSno();
		$this->addPaginator(5);


	}

	// function formatRow(){
	// 	parent::formatRow();
	// }
}