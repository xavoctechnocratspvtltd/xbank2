<?php

class Grid_VoucherSearch extends Grid_AccountsBase{
	
	public $voucher_no=0;
	public $branch=0;

	function init(){
		parent::init();
		$this->addclass('report-font report-margin');
		// $this->addclass('report-margin');
	}
	function setModel($model,$fields=array()){
		parent::setModel($model,$fields);
		$this->addFormatter('voucher_no','smallWrap');
		$this->addFormatter('account','400Wrap');
		$this->addFormatter('amountDr','80Wrap');
		$this->addFormatter('amountCr','80Wrap');
		$this->addTotals(array('amountDr','amountCr'));
	}

	function format_voucherNo($field){
		if($this->voucher_no==$this->model->get('voucher_no') && $this->branch==$this->model->get('branch_id'))
			$this->current_row[$field]=$this->model->get('Narration');
		else{
			$this->voucher_no=$this->model->get('voucher_no');
			$this->branch=$this->model->get('branch_id');
		}
		parent::format_voucherNo($field);
	}


}