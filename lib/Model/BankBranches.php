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
		$this->hasMany('Member','bankbranch_a_id',null,'AsFirstBank');
		$this->hasMany('Member','bankbranch_a_id',null,'AsSecondBank');
		$this->addHook('beforeDelete',$this);
		// $this->add('dynamic_model/Controller_AutoCreator');
	}

	function beforeDelete(){
		if($this->ref('AsFirstBank')->count()->getOne() > 0){
			throw new \Exception("can not Delete, First Delete related member", 1);
			
		}
		if($this->ref('AsSecondBank')->count()->getOne() > 0){
			throw new \Exception("can not Delete, First Delete related member", 1);

		}
	}
}