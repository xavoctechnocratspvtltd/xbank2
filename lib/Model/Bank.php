<?php

class Model_Bank extends Model_Table {
	public $table = "bank";

	function init(){
		parent::init();

		$this->addField('name');

		$this->hasMany('BankBranches','bank_id');
		$this->addHook('beforeDelete',$this);
		// $this->add('dynamic_model/Controller_AutoCreator');
	}

	function beforeDelete(){
		if($this->ref('BankBranches')->count()->getOne() > 0){
			throw new \Exception("Can not Delete Bank , First Delete This Bank Branches", 1);
			
		}
	}
}