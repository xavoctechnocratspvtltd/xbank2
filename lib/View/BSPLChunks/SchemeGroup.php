<?php

class View_BSPLChunks_SchemeGroup extends View {
	public $under_balance_sheet_id=null;
	public $from_date=null;
	public $to_date=null;
	public $branch=null;

	function init(){
		parent::init();
		if(!$this->under_balance_sheet_id)
			throw $this->exception('under_balance_sheet_id is must');

		$bs= $this->add('Model_BalanceSheet')->load($this->under_balance_sheet_id);

		$result = $this->add('Model_Scheme')->getOpeningBalanceByGroup($this->api->nextDate($this->to_date),$forPandL=$bs['is_pandl'],$this->branch,$bs,array('SchemeGroup','scheme','SchemeGroup'));

		$grid = $this->add('Grid_BalanceSheet',array('from_date'=>$this->from_date, 'to_date'=>$this->to_date));
		$grid->setSource($result);

		$grid->addColumn('text,SchemeGroupToSchemeName','SchemeGroup');
		$grid->addColumn('money','Amount');
		$grid->removeColumn('id');

		$grid->addTotals(array('Amount'));

		
	}
}