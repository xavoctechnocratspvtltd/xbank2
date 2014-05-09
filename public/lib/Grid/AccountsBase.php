<?php


class Grid_AccountsBase extends Grid{
	public $sno=1;
	public $order=null;
	public $opening_balance = 0;
	public $current_row_balance = 0;

	function init(){
		parent::init();
		$this->order = $this->addOrder();
	}

	function addSno(){
		$this->addColumn('sno','s_no');
		$this->order->move('s_no','first');
	}

	function format_sno($field){
		$this->current_row[$field] = (($this->sno++) + ($_GET[$this->short_name.'_paginator_skip']?:0));
	}

	function init_voucherNo($field){
	}

	function format_voucherNo($field){
		$url = $this->api->url('voucher_print');
		$transaction_id = ($this->model instanceof Model_TransactionRow)? $this->model['transaction_id']: $this->model->id;
		$this->current_row_html[$field] = "<a href='#' class='voucher' onclick='$(this).univ().frameURL(\"Transaction Voucher\",\"" .
                    $url->set(array(
                        $field => $this->current_id,
                        'transaction_id'=>$transaction_id,
                        $this->name.'_'.$field => $this->current_id
                    )) . "\")'".
            ">".$this->current_row[$field]."</a>";
	}

	function recursiveRender(){
		if($this->order) $this->order->now();
		if($this->hasColumn('voucher_no')) $this->addFormatter('voucher_no','voucherNo');
		parent::recursiveRender();
	}

	function addCurrentBalanceInEachRow($title='Balance',$position='last',$negative='CR',$cr_column='amountCr',$dr_column='amountDr'){
		$this->required_current_balance = true;
		$this->addColumn($title);
		$this->order->move($title,$position);
		$this->addHook('formatRow',function($grid)use($title,$cr_column,$dr_column,$negative){
			$grid->current_row_balance = $grid->current_row_balance  + ($grid->current_row[$dr_column] - $grid->current_row[$cr_column]);
			if($grid->current_row_balance < 0 )
				$grid->current_row[$title]= abs($grid->current_row_balance) ." ". $negative;
			else
				$grid->current_row[$title]=abs($grid->current_row_balance) . " ". ($negative=='CR' ?'DR':'CR');
		});
	}

	function addOpeningBalance($amount,$column, $other_columns=array(),$opening_side){

		if(isset($this->required_current_balance))
			throw $this->exception('If Current balance in each row is required then please add this function before addCurrentBalanceInEachRow function','Logic');

		$this->addHook('formatRow',function($grid)use($amount,$column,$other_columns, $opening_side){
			if($grid->sno == 1){
				$other_columns[$column] = $amount;
				foreach ($grid->columns as $name => $other_data) {
					if(!in_array($name, array_keys($other_columns)))
						$other_columns[$name] = '';
					else
						$other_columns[$name] = $other_columns[$name];
				}
				$grid->insertBefore($other_columns);
				$grid->opening_balance = $amount;
				$grid->opening_side= $opening_side;

				$grid->current_row_balance = $amount * (($opening_side=='DR')?1:-1); //Assuming Cr to be negative in all software
			}
		});
	}

	function insertBefore($data){
		$saved_current_id = $this->current_id;
		$saved_current_row = $this->current_row;
		$saved_current_row_html = $this->current_row_html;
		foreach ($data as $key => $value) {
			$this->current_row_html[$key]=$value;
		}
		$this->template->appendHTML($this->container_tag,$this->rowRender($this->row_t));
		
		$this->current_id= $saved_current_id;
		$this->current_row = $saved_current_row;
		$this->current_row_html=$saved_current_row_html;
	}

}