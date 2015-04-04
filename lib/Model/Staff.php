<?php

class Model_Staff extends Model_Table {
	var $table= "staffs";

	function init(){
		parent::init();

		$this->hasOne('Branch','branch_id')->mandatory(true);

		$this->addField('name')->mandatory(true);
		$this->addField('username')->mandatory(true);
		$this->addField('password');
		$this->addField('father_name')->mandatory(true);
		$this->addField('pf_amount');
		$this->addField('basic_pay');
		$this->addField('variable_pay');
		$this->addField('created_at')->caption('Joining Date');
		$this->addField('present_address');
		$this->addField('parmanent_address');
		$this->addField('mobile_no');
		$this->addField('landline_no');
		$this->addField('DOB');

		$this->hasMany('Transaction','staff_id');

		$this->addField('AccessLevel')->setValueList(array('100'=>'Super Admin','80'=>'CEO','60'=>'Branch Admin','40'=>'Power Staff', '20'=>'Staff','10'=>'Guest'))->mandatory(true)->DefaultValue(20);
		$this->addHook('beforeDelete',$this);

		// $this->add('dynamic_model/Controller_AutoCreator');
	}

	function beforeDelete(){
		
	}

	function isSuper(){
		return $this['AccessLevel']==100;
	}

	function isCEO(){
		return $this['AccessLevel'] == 80;
	}

	function createNewStaff($name,$password,$AccessLevel,$branch_id=null){
		if(!$branch_id) $branch_id = $this->api->current_branch->id;
		if($this->loaded()) throw $this->exception('Use Empty Model to create new Staff');
		
		$this['username']= $name;
		$this['name'] = $name;
		$this['password'] = $password;
		$this['AccessLevel'] = $AccessLevel;
		$this['branch_id'] = $branch_id;
		$this->save();
	}

	function delete($forced=false){
		if(!$forced){
			if($this->ref('Transaction')->count()->getOne() > 0)
				throw $this->exception('Cannot delete Staff, contains transactions done by staff');
		}

		foreach($t=$this->ref('Transaction') as $t_array){
			$t->delete($forced,true);
		}
		parent::delete();
	}

	
}