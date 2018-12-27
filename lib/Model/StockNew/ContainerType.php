<?php

class Model_StockNew_ContainerType extends Model_Table {
	var $table= "stocknew_containertype";
	
	function init(){
		parent::init();

		$this->addField('name')->sortable(true);
		
		$this->hasMany('StockNew_Container','containertype_id');
		$this->addHook('beforeDelete',$this);	
		$this->add('dynamic_model/Controller_AutoCreator');
	}

	function beforeDelete($model){
		if($this->ref('StockNew_Container')->count()->getOne() > 0)
			$this->api->js()->univ()->errorMessage('Category ('.$model['name'].') Contains Containers(s), Cannot Delete')->execute();
	}
	
}