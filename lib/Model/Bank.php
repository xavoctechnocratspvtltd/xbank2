<?php

class Model_Bank extends Model_Table {
	public $table = "bank";

	function init(){
		parent::init();

		$this->addField('name');

		$this->hasMany('BankBranches','bank_id');

		// $this->add('dynamic_model/Controller_AutoCreator');
	}
}