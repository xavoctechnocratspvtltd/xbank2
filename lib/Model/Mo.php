<?php

class Model_Mo extends Model_Table {
	public $table = 'mos';

	function init(){
		parent::init();

		$this->hasOne('ActiveBranch','branch_id');
		$this->addField('name');
		$this->addHook('beforeDelete',$this);
		
		$this->hasMany('Account','mo_id');
		$this->add('dynamic_model/Controller_AutoCreator');
	}

	function beforeDelete(){

	}

}