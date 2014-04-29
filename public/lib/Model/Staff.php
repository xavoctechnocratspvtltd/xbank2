<?php

class Model_Staff extends Model_Table {
	var $table= "staffs";

	function init(){
		parent::init();

		$this->hasOne('Branch','branch_id')->mandatory(true);

		$this->addField('name')->mandatory(true);
		$this->addField('password');

		$this->addField('AccessLevel')->setValueList(array('100'=>'Super Admin','80'=>'CEO','60'=>'Branch Admin','40'=>'Power Staff', '20'=>'Staff','10'=>'Guest'));

		$this->addHook('beforeSave',function($model){
			echo 'I am called';
		});

		//$this->add('dynamic_model/Controller_AutoCreator');
	}
}