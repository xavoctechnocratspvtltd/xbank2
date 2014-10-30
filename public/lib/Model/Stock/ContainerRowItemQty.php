<?php

class Model_Stock_ContainerRowItemQty extends Model_Table {
	var $table= "stock_containerrowitemqty";
	
	function init(){
		parent::init();

		$this->hasOne('Branch','branch_id');
		$this->addCondition('branch_id',$this->api->current_branch->id);
		$this->hasOne('Stock_Container','container_id')->defaultValue('Null');
		$this->hasOne('Stock_Row','row_id')->defaultValue('Null');
		$this->hasOne('Stock_Item','item_id')->defaultValue('Null');

		$this->addField('name')->caption('qty');
		
		$this->add('dynamic_model/Controller_AutoCreator');
	}

	function createNew($qty,$other_fields=array(),$form=null){

		if($this->loaded())
			throw $this->exception('Please call on loaded Object');
		$this['name']=$qty;
		$this['row_id']=$other_fields['row_id'];
		$this['item_id']=$other_fields['item_id'];
		$this->save();
	}

}