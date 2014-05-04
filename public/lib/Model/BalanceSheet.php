<?php
class Model_BalanceSheet extends Model_Table {
	var $table= "balance_sheet";
	function init(){
		parent::init();

		$this->addExpression('name')->set('Head');

		//$this->add('dynamic_model/Controller_AutoCreator');
	}
}