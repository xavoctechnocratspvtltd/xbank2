<?php

class Model_Stock_Row extends Model_Table {
	var $table= "stock_rows";
	function init(){
		parent::init();

		$this->hasOne('Branch','branch_id');
		$this->addCondition('branch_id',$this->api->current_branch->id);

		$this->hasOne('Stock_Container','container_id');
		
		$this->addField('name');
		
		$this->hasMany('Stock_ContainerRowItemQty','row_id');

		$this->add('dynamic_model/Controller_AutoCreator');
	}


	function createNew($name,$other_fields=array(),$form=null){

		if($this->loaded())
			throw $this->exception('Please call on empty Object');
		
		$this['name']=$name;
		$this['container_id']=$other_fields['container_id'];
		$this->save();
	}

	function beforeDelete(){
		if($this->ref('Stock_Item')->count()->getOne() > 0)
			throw $this->exception('You can not delete this Row it Contain Items');
	}

	function remove(){
		if(!$this->loaded())
			throw $this->exception('Unable To determine the record to be delete');
		$this->delete();
	}

	function removeItem($item){
		if(!$this->loaded())
			throw $this->exception('Unable To determine the container, Please Specify');
		if(!$item->loaded())
			throw $this->exception('Unable To determine the Item To be removed');
		if($i=$this->hasItem($item))
			$i->remove();
	}

	function allItems(){
		if($this->loaded())
			throw $this->exception('Plaese call On loaded object of Row');
		$this->ref('Stock_Item');
	}

	function addItem(){
		
	}

	function moveItem(){
		
	}

}