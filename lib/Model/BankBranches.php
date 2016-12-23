<?php

class Model_BankBranches extends Model_Table {
	public $table = "bank_branches";

	function init(){
		parent::init();

		$this->hasOne('Bank','bank_id');
		
		$this->addField('name');
		$this->addField('IFSC');

		$this->addExpression('full_name')->set(function($m,$q){
			return $q->expr('CONCAT(IFNULL(name,"")," [",IFNULL([0],""),"] : ",IFNULL(IFSC,""))',[
					$m->refSQL('bank_id')->fieldQuery('name')
				]);
		});
		$this->hasMany('Member','bank_a_id',null,'AsFirstBank');
		$this->hasMany('Member','bank_b_id',null,'AsSecondBank');

		// $this->add('dynamic_model/Controller_AutoCreator');
	}
}