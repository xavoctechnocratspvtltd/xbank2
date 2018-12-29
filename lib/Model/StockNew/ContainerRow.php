<?php

class Model_StockNew_ContainerRow extends Model_Table {
	var $table= "stocknew_containerrow";
	
	function init(){
		parent::init();

		$this->hasOne('StockNew_Container','container_id');
		$this->addField('name')->sortable(true);
		$this->addField('is_default')->type('boolean')->defaultValue(false);
		
		$this->addExpression('branch_id')->set($this->refSQL('container_id')->fieldQuery('branch_id'));


		$this->addHook('beforeSave',$this);	
		$this->add('dynamic_model/Controller_AutoCreator');
	}

	function beforeSave(){
		if($this->isDirty('is_default') && $this['is_default']==true){
			$container_branch = $this->add('Model_StockNew_Container')->load($this['container_id'])->get('branch_id');
			$existing_default_rows_of_branch = $this->add('Model_StockNew_ContainerRow')
				->addCondition('branch_id',$container_branch)
				->addCondition('is_default',true)
				->count()->getOne();
			
			if($existing_default_rows_of_branch >= 1){
				throw new \Exception("Each Branch can only have one default row of any container", 1);
			}
			
		}
	}

	function loadDefault($branch_id){
		$this->addCondition('branch_id',$branch_id);
		$this->addCondition('is_default',true);
		$this->loadAny();
	}

	// function beforeDelete($model){
	// 	if($this->ref('StockNew_ContainerRow')->count()->getOne() > 0)
	// 		$this->api->js()->univ()->errorMessage('Category ('.$model['name'].') Contains Rows(s), Cannot Delete')->execute();
	// }
	
}