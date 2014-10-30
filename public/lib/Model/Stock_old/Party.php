<?php

class Model_Stock_Party extends Model_Table {
	var $table= "stock_parties";
	function init(){
		parent::init();
		$this->addField('name');
		$this->addField('address');
		$this->addField('ph_no');
		$this->add('dynamic_model/Controller_AutoCreator');
	}

	function createNew($name,$other_fields=array(),$form=null){

		if($this->loaded())
			throw $this->exception('Please call on loaded Object');
		$this['name']=$name;
		$this['address']=$other_fields['address'];
		$this['ph_no']=$other_fields['ph_no'];
		$this->save();
	}
}