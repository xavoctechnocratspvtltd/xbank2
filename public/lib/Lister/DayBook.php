<?php

class Lister_DayBook extends Grid{
	public $voucher_no=0;

	function setModel($model,$fields=array()){
		parent::setModel($model,$fields);
		$this->addFormatter('voucher_no','voucherNo');
	}

	function format_voucherNo($field){
		if($this->voucher_no==$this->model->get('voucher_no'))
			$this->current_row[$field]=$this->model->get('Narration');
		else{
			$this->voucher_no=$this->model->get('voucher_no');
		}
	}
}