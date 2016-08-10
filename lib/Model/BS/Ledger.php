<?php



class Model_BS_Ledger extends Model_Account {
	
	public $from_date=null;
	public $to_date=null;
	public $branch_id=null;
	
	function init(){
		parent::init();
		
		$this->scheme_join->addField('balance_sheet_id');

	}
}