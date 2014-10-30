<?php

class Model_Stock_Item extends Model_Table {
	var $table= "stock_items";
	function init(){
		parent::init();

		$this->hasOne('Branch','branch_id');
		$this->addCondition('branch_id',$this->api->current_branch->id);
		$this->hasOne('Stock_Category','category_id');
		
		$this->addField('name');
		$this->addField('description')->type('text');
		$this->addField('is_consumable')->type('boolean')->defaultValue(false);
		$this->addField('is_issueable')->type('boolean')->defaultValue(false);
		$this->addField('is_fixedassets')->type('boolean')->defaultValue(false);
		
		$this->hasMany('Stock_Transaction','item_id');
		$this->hasMany('Stock_ContainerRowItemQty','item_id');

		$this->_dsql()->order('name','asc');

		$this->add('dynamic_model/Controller_AutoCreator');
	}


	function createNew($name,$other_fields=array(),$form=nulll){
		if($this->loaded())
			throw $this->exception('This Function create New Items, So please pass Empty Object');
		
		$this['name']=$name;
		$this['category_id']=$other_fields['category_id'];
		$this['is_consumable']=$other_fields['is_consumable'];
		$this['is_issueable']=$other_fields['is_issueable'];
		$this['is_fixedassets']=$other_fields['is_fixedassets'];
		$this->save();
	}

	function remove(){
		if(!$this->loaded())
			throw $this->exception('Unable To determine The Recored to be delete ');
		$this->delete();
	}

	function isExistInRow($row){
		if(!$this->loaded())
			throw $this->exception('Item model is not loaded');
			
		if(!$row->loaded())
			throw $this->exception('Please pass loaded object of Row');

		$this->addCondition('row_id',$row->id);
		$this->tryLoadAny();
		if($this->loaded())
			return $this;
		else 
			return false;
	}

	function markConsumeable(){
		if(!$this->loaded())
			throw new Exception("Item Model is not Loaded");
		$this['is_consumable']=true;
		$this->save();
	}

	function markIssuable(){
		if(!$this->loaded())
			throw new Exception("Item Model is not Loaded");
		$this['is_issueable']=true;
		$this->save();
	}

	function markFixedAssest(){
		if(!$this->loaded())
			throw new Exception("Item Model is not Loaded");
		$this['is_fixedassets']=true;
		$this->save();
	}
	
	function issue(){
		// todo 
	}

	function submit(){
		// todo 
	}

	function dead(){
		// todo 
	}

	function transfer($item,$row,$container,$qty){
		$row_model = $this->add('Model_Stock_Row');
		$row_model->moveItem($item,$qty);
		$row_model->addItem($item,$qty);
	}

	function opening(){
		// todo
	}

	function sold(){
		// todo
	}

	function getAvgRate(){
		// todo
	}

	function getDetail(){
		// Container row item Qty detail

	}

	function getIssue(){
		// todo
	}

	function getConsume(){
		// todo
	}

	function isDead(){
		// todo
	}


	function isConsume(){
		// todo
	}

	function getDeadQty(){
		// todo
	}	

	function getAllPurchase(){
		// todo
	}


}