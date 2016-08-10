<?php



class Model_BS_Ledger extends Model_Account {
	
	public $from_date=null;
	public $to_date=null;
	public $branch_id=null;
	
	function init(){
		parent::init();

		$this->addExpression('balance_sheet_id')->set(function($m,$q){
			return $m->refSQL('scheme_id')->fieldQuery('balance_sheet_id');
		});

	}
}