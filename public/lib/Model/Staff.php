<?php

class Model_Staff extends Model_Table {
	var $table= "staffs";

	function init(){
		parent::init();

		$this->hasOne('Branch','branch_id')->mandatory(true);

		$this->addField('name')->mandatory(true);
		$this->addField('password');

		$this->addField('AccessLevel')->setValueList(array('100'=>'Super Admin','80'=>'CEO','60'=>'Branch Admin','40'=>'Power Staff', '20'=>'Staff','10'=>'Guest'));
		//$this->add('dynamic_model/Controller_AutoCreator');
	}

	function createNewStaff($name,$password,$AccessLevel,$branch_id=null){
		if(!$branch_id) $branch_id = $this->api->current_branch->id;
		if($this->loaded()) throw $this->exception('Use Empty Model to create new Staff');
		
		$this['name'] = $name;
		$this['password'] = $password;
		$this['AccessLevel'] = $AccessLevel;
		$this['branch_id'] = $branch_id;
		$this->save();
	}
}