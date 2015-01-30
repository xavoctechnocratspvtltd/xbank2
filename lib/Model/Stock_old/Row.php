<?php

class Model_Stock_Row extends Model_Table {
	var $table= "stock_rows";
	function init(){
		parent::init();
		$this->hasOne('Stock_Container','container_id');
		$this->addField('name');
		$this->hasMany('Stock_Item','item_id');
		
		$this->addHook('beforeSave',$this);

		$this->add('dynamic_model/Controller_AutoCreator');
	}

	function beforeSave(){
		$tmp = $this->add('Model_Stock_Row');
		$tmp->addCondition('name',$this['name']);
		$tmp->addCondition('container_id',$this['container_id']);

		if($this->loaded()){
			$tmp->addCondition('id','<>',$this->id);
		}

		$tmp->tryLoadAny();
		if($tmp->loaded())
			throw $this->exception('Name Already Exists','ValidityCheck')->setField('name');
	}

	function createNew($name,$other_fields=array(),$form=null){

		if($this->loaded())
			throw $this->exception('Please call on empty Object');
		
		$this['name']=$name;
		$this['container_id']=$other_fields['container_id'];
		$this->save();
	}

	function remove(){
		if(!$this->loaded())
			throw $this->exception('Unable To determine the record to be delete');
		$this->delete();
	}

	function beforeDelete(){
		if($this->ref('Stock_Item')->count()->getOne() > 0)
			throw $this->exception('You can not delete this Row it Contain Items');
	}

	function addItem($name,$category,$row,$other_fields=null){

		if(!$this->loaded())
			throw $this->exception('Unable To determine the Row for adding Item, Please specify');
		if(!$container->loaded())
			throw $this->exception('Please Pass Loaded Object of Container');
		if(!$category->loaded())
			throw $this->exception('Please Pass Loaded Object of Category');

		$item=$this->add('Model_Item');
		$item->createNew($name,$category,$container,$other_fields);


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

	function isExist($container){
		if(!$this->loaded())
			throw $this->exception('Please pass loaded object of Row');
			
		if(!$container->loaded())
			throw $this->exception('Please pass loaded object of container');
		$this->addCondition('container_id',$container->id);
		$this->tryLoadAny();
		if($this->loaded())
			return $this;
		else 
			return false;

	}

	function hasItem($item){
		if(!$item->loaded())
			throw $this->exception('Please pass loaded object of Item');
		if(!$this->loaded())
			throw $this->exception('Please pass loaded object of Item');
		return $item->isExistInRow($this);

	}


}