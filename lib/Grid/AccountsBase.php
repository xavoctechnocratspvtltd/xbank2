<?php


class Grid_AccountsBase extends Grid{
	
	public $order=null;
	public $opening_balance = 0;
	public $current_row_balance = 0;
	public $skip_var=null;
	function init(){
		parent::init();
		$this->order = $this->addOrder();
	}

	function init_voucherNo($field){
	}

	function format_voucherNo($field){
		$url = $this->api->url('voucher_print');
		$transaction_id = ($this->model instanceof Model_TransactionRow)? $this->model['transaction_id']: $this->model->id;
		$this->current_row_html[$field] = "<a href='#voucher' class='voucher' onclick='$(this).univ().frameURL(\"Transaction Voucher ". $this->model['voucher_no'] ." uuid:". $this->model->id ."\",\"" .
                    $url->set(array(
                        $field => $this->current_id,
                        'transaction_id'=>$transaction_id,
                        $this->name.'_'.$field => $this->current_id
                    )) . "\")'".
            ">".$this->current_row[$field]."</a>";
	}

	function format_picture($field){
		// $vp = $this->add('VirtualPage',array('name'=>'pic_'.$this->model->id));
		// $vp->set(function($p){
		// 	$p->add('View')->setElement('img')->setAttr('src',$_GET[$p->name.'_src']);
		// });
		$this->current_row_html[$field] = '<img src="'.$this->current_row[$field].'" width="100px" class="grid_picture"/>';
	}

	function format_smallWrap($field){
		$this->setTDParam($field, 'style', 'width:300px;text-wrap:suppress;');
		// $this->current_row_html[$field] = '<div style="width:50px;">'.$this->current_row[$field].'</div>';
	}function format_400Wrap($field){
		$this->setTDParam($field, 'style', 'width:400px;text-wrap:suppress;');
		// $this->current_row_html[$field] = '<div style="width:50px;">'.$this->current_row[$field].'</div>';
	}
	function format_200Wrap($field){
		$this->setTDParam($field, 'style', 'width:200px;text-wrap:suppress;');
		// $this->current_row_html[$field] = '<div style="width:50px;">'.$this->current_row[$field].'</div>';
	}
	function format_100Wrap($field){
		$this->setTDParam($field, 'style', 'width:100px;text-wrap:suppress;');
		// $this->current_row_html[$field] = '<div style="width:50px;">'.$this->current_row[$field].'</div>';
	}
	function format_80Wrap($field){
		$this->setTDParam($field, 'style', 'width:80px;text-wrap:suppress;');
		// $this->current_row_html[$field] = '<div style="width:50px;">'.$this->current_row[$field].'</div>';
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
			if($grid->hasColumn('voucher_no') && !$grid->current_row['voucher_no']) return;

			$grid->current_row_balance = round($grid->current_row_balance  + (($grid->current_row[$dr_column]?:0) - ($grid->current_row[$cr_column]?:0)),3);
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

	function addOpeningBalance_TEST($amount,$column, $other_columns=array(),$opening_side){

		if(isset($this->required_current_balance))
			throw $this->exception('If Current balance in each row is required then please add this function before addCurrentBalanceInEachRow function','Logic');

		$this->addHook('xyz',function($grid)use($amount,$column,$other_columns, $opening_side){
			// if($grid->sno == 1){
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
			// }
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