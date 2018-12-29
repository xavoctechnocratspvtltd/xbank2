<?php

class Model_StockNew_Category extends Model_Table {
	var $table= "stocknew_category";
	
	function init(){
		parent::init();

			
		$this->addField('name')->sortable(true);
		$this->addField('allowed_in_transactions');

		$this->hasMany('StockNew_Item','category_id');
		$this->addHook('beforeSave',$this);
		$this->addHook('beforeDelete',$this);
		$this->add('dynamic_model/Controller_AutoCreator');
	}

	function beforeSave(){
		
	}

	function beforeDelete($model){
		if($this->ref('StockNew_Item')->count()->getOne() > 0)
			$this->api->js()->univ()->errorMessage('Category ('.$model['name'].') Contains Item(s), Cannot Delete')->execute();
	}
	
}