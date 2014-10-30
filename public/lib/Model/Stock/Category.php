<?php

class Model_Stock_Category extends Model_Table {
	var $table= "stock_categories";
	
	function init(){
		parent::init();

		$this->hasOne('Branch','branch_id');
		$this->addCondition('branch_id',$this->api->currentBranch->id);
			
		$this->addField('name');
		
		$this->hasMany('Stock_Item','item_id');
		
		$this->add('dynamic_model/Controller_AutoCreator');
	}

	function createNew($name,$other_fields=array(),$form=null){

		if($this->loaded())
			throw $this->exception('Please call on loaded Object');
		$this['name']=$name;
		$this->save();
	}

	function getAllItem($category){
		
		if(!$category instanceof Model_Stock_Category)
			throw new Exception("Model is not a instanceof Category model");
			
		$item_model = $this->add('Model_Stock_Item');
		$item_model->addCondition('category_id',$category->id);
		$item_model->tryLoadAny();
		return $item_model;
	}		

	
}