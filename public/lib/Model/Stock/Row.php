<?php

class Model_Stock_Row extends Model_Table {
	var $table= "stock_rows";
	function init(){
		parent::init();

		$this->hasOne('Branch','branch_id');
		$this->addCondition('branch_id',$this->api->current_branch->id);

		$this->hasOne('Stock_Container','container_id');
		
		$this->addField('name')->mandatory(true);
		
		$this->hasMany('Stock_ContainerRowItemQty','row_id');

		$this->addHook('beforeSave',$this);
		$this->addHook('beforeDelete',$this);
		$this->add('dynamic_model/Controller_AutoCreator');
	}


	function createNew($name,$other_fields=array(),$form=null){

		if($this->loaded())
			throw $this->exception('Please call on empty Object');
		
		$this['name']=$name;
		$this['container_id']=$other_fields['container_id'];
		$this->save();
	}

	function beforeSave($row){
			
		$tmp = $this->add('Model_Stock_Row');
		$tmp->addCondition('branch_id',$this['branch_id']);
		$tmp->addCondition('container_id',$row['container_id']);
		$tmp->addCondition('name',$this['name']);

		if($this->loaded()){
			$tmp->addCondition('id','<>',$this->id);
		}

		$tmp->tryLoadAny();
		if($tmp->loaded())			
			throw $this->exception('Name Already Exists in this Conatiner','ValidityCheck')->setField('name');

	}	

	function beforeDelete(){

		if($this['name']=='General' AND $this['container']=='General')
			throw $this->exception('You can not Delete this Row, It is system Generated');

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

	function loadGeneralRow(){
		
		$cntr_model = $this->add('Model_Stock_Container');
		$cntr_model->loadGeneralContainer();
		$this->addCondition('container_id',$cntr_model->id);
		$this->addCondition('name','General');
		$this->tryLoadAny();
		return $this;
	}

	function loadRow($row,$container_id){		
		$row_model = $this->add('Model_Stock_Row');
		$this->addCondition('id',$row);
		$this->addCondition('container_id',$container_id);
		$this->tryLoadAny();
		if($this->loaded()){
			return $this;	
		}else
			throw $this->exception("Row( ".$row.") not Exits");			
	}

}