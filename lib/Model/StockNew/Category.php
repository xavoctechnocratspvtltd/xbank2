<?php

class Model_StockNew_Category extends Model_Table {
	var $table= "stocknew_category";
	
	function init(){
		parent::init();

			
		$this->addField('name')->sortable(true);
		
		$this->hasMany('StockNew_Item','category_id');
		$this->addHook('beforeDelete',$this);	
		$this->add('dynamic_model/Controller_AutoCreator');
	}

	function getAllItem($category){
		
		if(!$category instanceof Model_Stock_Category)
			throw new Exception("Model is not a instanceof Category model");
			
		$item_model = $this->add('Model_StockNew_Item');
		$item_model->addCondition('category_id',$category->id);
		$item_model->tryLoadAny();
		return $item_model;
	}		

	function beforeDelete($model){
		if($this->ref('StockNew_Item')->count()->getOne() > 0)
			$this->api->js()->univ()->errorMessage('Category ('.$model['name'].') Contains Item(s), Cannot Delete')->execute();
	}
	
}