<?php

class View_BSPLChunks_Schemes extends View {
	public $under_balance_sheet=null;
	public $under_scheme_group=null;

	public $from_date=null;
	public $to_date=null;
	public $branch=null;

	function init(){
		parent::init();
		
		if($this->under_balance_sheet)
			$this->balancesheetToScheme();

		if($this->under_scheme_group)
			$this->schemeGroupToScheme();

		
	}

	function schemeGroupToScheme(){
		$schemes = $this->add('Model_Scheme');
		$schemes->join('balance_sheet','balance_sheet_id')->addField('is_pandl');
		$schemes->addCondition('SchemeGroup',$this->under_scheme_group);

		$result_array=array();
		foreach ($schemes as $s) {
			$op_bal = $s->getOpeningBalance($this->api->nextDate($this->to_date),$side='both',$forPandL=$schemes['is_pandl'],$branch=$this->branch);
			$result_array[] = array('Scheme'=>$s['name'],'Amount'=>$op_bal['Dr']-$op_bal['Cr']);
		}

		$grid = $this->add('Grid_BalanceSheet');
		$grid->from_date = $this->from_date;
		$grid->to_date = $this->to_date;
		
		$grid->setSource($result_array);

		$grid->addColumn('text,SchemeNameToAccounts','Scheme');
		$grid->addColumn('money','Amount');

		$grid->removeColumn('id');

		$grid->addTotals(array('Amount'));
	}

}