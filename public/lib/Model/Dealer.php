<?php
class Model_Dealer extends Model_Table {
	var $table= "dealers";
	function init(){
		parent::init();

		$this->addField('name');
		$this->addField('Address');
		$this->addField('loan_panelty_per_day');
		//$this->add('dynamic_model/Controller_AutoCreator');
	}
}