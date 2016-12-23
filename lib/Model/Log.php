<?php

class Model_Log extends Model_Table {
	var $table= "xLog";
	function init(){
		parent::init();

		$this->hasOne('Staff','staff_id')->defaultValue($this->api->auth->model->id);

		$this->addField('model_class');
		$this->addField('pk_id')->type('int');

		$this->addField('created_at')->type('datetime')->defaultValue(date('Y-m-d H:i:s'));

		$this->addField('name')->type('text');
		$this->addField('type');

		$this->add('dynamic_model/Controller_AutoCreator');
	}

	function logFieldEdit($model,$record_id,$edit_what_field,$old_value,$new_value){

	}
}