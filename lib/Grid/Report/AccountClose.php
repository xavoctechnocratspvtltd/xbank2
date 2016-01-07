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
		$paginator = $this->addPaginator(500);
		$this->skip_var = $paginator->skip_var;

		$this->addFormatter('member','Wrap');
		$this->addFormatter('PermanentAddress','Wrap');
		$this->addColumn('status');
		$this->addFormatter('status','status');

		$this->removeColumn('ActiveStatus');
	}

	function format_status($fields){
		if($this->model['ActiveStatus'] == 0)
			$this->current_row_html[$fields] = "De-Active";
		else
			$this->current_row_html[$fields] = "Active";
	}

	// function formatRow(){
	// 	if($this->model['ActiveStatus'] == 0)
	// 		$this->current_row_html['ActiveStatus'] = "De-active";
	// 	else
	// 		$this->current_row_html['ActiveStatus'] = "Active";
	// 	parent::formatRow();
		
	// }
}