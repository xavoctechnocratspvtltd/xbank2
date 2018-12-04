<?php


class Model_TeleCaller extends Model_Table {
	public $table = "telecaller";

	function init(){
		parent::init();

		$this->addField('name');
		$this->addField('is_active')->type('boolean')->defaultValue(true);

		$this->hasMany('Account','telecaller_id');

		$this->addHook('beforeDelete',$this);

	}

	function beforeDelete(){
		if($this->ref('Account')->count()->getOne()){
			throw new \Exception("Telecaller has assigned acocuns, cannot delete", 1);
			
		}
	}
}