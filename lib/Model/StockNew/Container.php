<?php

class Model_StockNew_Container extends Model_Table {
	var $table= "stocknew_container";
	
	function init(){
		parent::init();

		$this->hasOne('Branch','branch_id');
		$this->hasOne('StockNew_ContainerType','containertype_id');
		$this->addField('name')->sortable(true);
		
		$this->hasMany('StockNew_ContainerRow','container_id');
		$this->addHook('beforeDelete',$this);	
		$this->add('dynamic_model/Controller_AutoCreator');
	}

	function beforeDelete($model){
		if($this->ref('StockNew_ContainerRow')->count()->getOne() > 0)
			$this->api->js()->univ()->errorMessage('Category ('.$model['name'].') Contains Rows(s), Cannot Delete')->execute();
	}
	
}