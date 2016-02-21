<?php

class View_BSPLChunks_Accounts extends View {
	public $under_balance_sheet_id=null;
	public $under_scheme=null;
	public $under_pandl_group=null;

	public $from_date=null;
	public $to_date=null;
	public $branch=null;

	function init(){
		parent::init();
		
		if($this->under_balance_sheet_id and !$this->under_scheme)
			return $this->from_balancesheet_to_accounts();

		if($this->under_scheme)
			return $this->from_scheme_to_accounts();

		if($this->under_pandl_group)
			return $this->from_pandlgroup_to_accounts();

		throw $this->exception('Either under_scheme or under_pandl_group or under_balance_sheet_id should be defined', 'ValidityCheck')->setField('FieldName');

	}

	function from_balancesheet_to_accounts(){
		// $this->add('View_Error')->set('Hello');
		// return;
		$bs= $this->add('Model_BalanceSheet')->load($this->under_balance_sheet_id);
		$result= $this->add('Model_Scheme')->getOpeningBalanceByGroup($this->api->nextDate($this->to_date),$forPandL=$bs['is_pandl'],$this->branch,$bs,array('AccountNumber','account','AccountNumber'),null,$this->from_date);
		$grid = $this->add('Grid_BalanceSheet');
		$grid->from_date = $this->from_date;
		$grid->to_date = $this->to_date;
		$grid->setSource($result);

		$grid->addColumn('text,toAccountStatement','AccountNumber');
		$grid->addColumn('money','Amount');

		$grid->removeColumn('id');

		$grid->addTotals(array('Amount'));
	}

	function from_scheme_to_accounts(){
		$bs= $this->add('Model_BalanceSheet')->load($this->under_balance_sheet_id);
		$scheme= $this->add('Model_Scheme')->loadBy('name',$this->under_scheme);

		$result= $this->add('Model_Scheme')->getOpeningBalanceByGroup($this->api->nextDate($this->to_date),$forPandL=$bs['is_pandl'],$this->branch,null,array('AccountNumber','account','AccountNumber'),$scheme, $this->from_date);

		$grid = $this->add('Grid_BalanceSheet');
		$grid->from_date = $this->from_date;
		$grid->to_date = $this->to_date;
		$grid->setSource($result);

		$grid->addColumn('text,toAccountStatement','AccountNumber');
		$grid->addColumn('money','Amount');

		$grid->removeColumn('id');

		$grid->addTotals(array('Amount'));
	}

}