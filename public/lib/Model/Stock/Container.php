<?php

class Model_Stock_Container extends Model_Table {
	var $table= "stock_containers";
	function init(){
		parent::init();

		$this->addField('name');
		$this->hasMany('Stock_Item','item_id');
		$this->hasMany('Stock_Row','container_id');
		
		$this->addHook('beforeDelete',$this);
		$this->add('dynamic_model/Controller_AutoCreator');

	}

	function createNew($name,$other_fields=array(),$form=null){

		if($this->loaded())
			throw $this->exception('Please call on loaded Object');
		$this['name']=$name;
		$this->save();
	}

	function remove(){
		if(!$this->loaded())
			throw $this->exception('Unable To determine the record to be delete');
		$this->delete();
	}

	function beforeDelete(){
		if($this->ref('Stock_Row')->count()->getOne()>0)
			throw $this->exception('Can Not delete this container, It contains rows');
	}

	function addRow($name){
		if(!$this->loaded())
			throw $this->exception('Unable To determine the container');
		$row=$this->add('Model_Row');
		$row->createNew($name,$this);			
	}

	function removeRow($row){
		if(!$this->loaded())
			throw $this->exception('Unable To determine the container, Please Specify');
		if(!$row->loaded())
			throw $this->exception('Unable To determine the Item To be removed');
		if($r=$this->hasRow($row))
			$r->remove();

	}

	function hasRow($row){
		if(!$row->loaded())
			throw $this->exception('Please pass loaded object of Row');
		if(!$this->loaded())
			throw $this->exception('Please call on  loaded object');
		return $row->isExist($this);

	}

	
	function allRows(){
		if(!$this->loaded())
			throw $this->exception('Unable To determine Container, Please Specify');
		return $this->ref('Stock_Row');
	}
}