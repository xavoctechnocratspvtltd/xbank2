<?php


class View_AccountSheet extends View {

	public $from_date=false;
	public $to_date=false;
	public $for_branch = null;
	public $pandl = false;

	function init(){
		parent::init();

		$this->add('H3')->set(date('d-M-Y',strtotime($this->from_date)) . ' to ' . date('d-M-Y',strtotime($this->to_date)))->setAttr('align','center');
		$this->addClass('report-font');
		$cols = $this->add('Columns');
		$left_col = $cols->addColumn(6)->addClass('col-width');
		$right_col = $cols->addColumn(6)->addClass('col-width');

		$left_col->setStyle('vertical-align','top');
		$right_col->setStyle('vertical-align','top');

		$left_array=array();
		$right_array=array();

		$bs=$this->add('Model_BalanceSheet')
			->addCondition('is_pandl',$this->pandl)
			->setOrder('order');

		if(!$this->pandl){
			$left_title = 'Liabilities';
			$right_title = 'Assets';
		}else{
			$left_title = 'Expenses';
			$right_title = 'Income';
		}
		foreach ($bs as $key => $value) {
			$op_bal = $bs->getOpeningBalance($this->api->nextDate($this->to_date),null,$this->pandl,$this->for_branch,($this->pandl?$this->from_date:null));
			$subtract_from = $bs['subtract_from'];
			$subtract_what = $subtract_from=='Cr'? 'Dr':'Cr';
			if((($bal = $op_bal[$subtract_from] - $op_bal[$subtract_what]) > 0 AND $bs['positive_side']=='LT') OR ($bal < 0 AND $bs['positive_side']=='RT')){
				$left_array[] =array('id'=>$bs->id,$left_title=>$bs['name'],'Amount'=>abs($bal),'Details'=>'');
			}else{
				// echo $bal .' '. $bs['name']. ' '. $op_bal['Cr']. ' ' . $op_bal['Dr'] . ' '. $bs['positive_side'] .'<br/>';
				$right_array[] =array('id'=>$bs->id,$right_title=>$bs['name'],'Amount'=>abs($bal),'Details'=>'');
			}
		}

		// Get Pand L Values
		$pandl_val = $bs->getPandLClosingValue($this->from_date,$this->to_date,$this->for_branch);
		if(($bal = $pandl_val['sdr'] - $pandl_val['scr']) < 0){
			$left_array[] =array('id'=>0,$left_title=>'Profit','Amount'=>abs($bal),'Details'=>'');
		}else{
			$right_array[] =array('id'=>0,$right_title=>'Loss','Amount'=>abs($bal),'Details'=>'');
		}

		// Fill Extra pad /rows in shorten grid
		if(count($left_array) < count($right_array)){
			$to_fill_in = 'left_array';
			$field=$left_title;
			$no_of_rows = count($right_array) - count($left_array);
		}else{
			$to_fill_in = 'right_array';
			$field = $right_title;
			$no_of_rows = count($left_array)- count($right_array) ;
		}

		// Fill rows now
		for ($i=1; $i <= $no_of_rows; $i++) { 
			${$to_fill_in}[] = array('id'=>$i+100,$field=>'-','Amount'=>0,'Details'=>'');
		}
		$left_grid = $left_col->add('Grid_BalanceSheet')->addClass('col-title-bold');
		$left_grid->addColumn('text',$left_title);
		$left_grid->addColumn('money','Amount');

		$left_grid->setSource($left_array);
		$left_grid->addTotals(array('Amount'));

		$right_grid = $right_col->add('Grid_BalanceSheet')->addClass('col-title-bold');
		$right_grid->addColumn('text',$right_title);
		$right_grid->addColumn('money','Amount');

		$right_grid->setSource($right_array);
		$right_grid->addTotals(array('Amount'));

		$left_grid->addColumn('Expander,details','Details');
		$right_grid->addColumn('Expander,details','Details');


	}
}