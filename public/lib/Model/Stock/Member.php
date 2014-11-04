<?php

class Model_Stock_Member extends Model_Table {
	var $table= "stock_members";

	function init(){
		parent::init();
		
		$this->hasOne('Branch','branch_id');
		$this->addCondition('branch_id',$this->api->current_branch->id);
		
		$this->addField('name');
		$this->addField('address');
		$this->addField('ph_no');
		$this->addField('type')->enum(array('Agent', 'Dealer', 'Party', 'Staff', 'Supplier'));	
		$this->addField('is_active')->type('boolean');
		
		$this->hasMany('Model_Stock_Transaction','member_id');
		$this->add('dynamic_model/Controller_AutoCreator');
	}

	function createNew($name,$other_fields=array(),$form=null){

		if($this->loaded())
			throw $this->exception('Please call on loaded Object');
		$this['name']=$name;
		$this['address']=$other_fields['address'];
		$this['ph_no']=$other_fields['ph_no'];
		$this['type']=$other_fields['type'];
		$this['is_active']=$other_fields['is_active'];
		$this->save();
	}
	
		
}