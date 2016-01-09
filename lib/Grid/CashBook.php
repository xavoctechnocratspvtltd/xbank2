<?php

class Grid_CashBook extends Grid_AccountsBase{
	public $voucher_no=0;
	function init(){
		parent::init();
		$this->addClass('report-font report-margin');
	}
	function setModel($model,$fields=array()){
		parent::setModel($model,$fields);
		$this->addFormatter('voucher_no','100Wrap');
		$this->addFormatter('created_at','100Wrap');
		// $this->addFormatter('account','Wrap');
		$this->addFormatter('Narration','smallWrap');
		$this->addTotals(array('amountDr','amountCr'));
	}

	// function addSno(){
	// 	$this->addColumn('sno','s_no');
	// 	$this->order->move('s_no','first');
	// }

	function format_sno($field){
		// if($this->model->loaded())
			$this->current_row_html[$field] = "<p class='atk-align-center'>".(($this->sno++) + ($_GET[$this->skip_var]));
		
		// $this->current_row[$field] = $this->skip_var;		
	}

	function format_voucherNo($field){
		if($this->voucher_no==$this->model->get('voucher_no'))
			$this->current_row[$field]=$this->model->get('Narration');
		else{
			$this->voucher_no=$this->model->get('voucher_no');
		}
		parent::format_voucherNo($field);
	}


}