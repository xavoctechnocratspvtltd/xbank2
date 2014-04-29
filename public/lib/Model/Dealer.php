<?php
class Model_Dealer extends Model_Table {
	var $table= "dealers";
	function init(){
		parent::init();

		$this->addField('name');
		$this->addField('Address');
		//$this->add('dynamic_model/Controller_AutoCreator');
	}
}