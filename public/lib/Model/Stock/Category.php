<?php

class Model_Stock_Category extends Model_Table {
	var $table= "stock_categories";
	function init(){
		parent::init();

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

}