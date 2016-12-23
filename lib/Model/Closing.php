<?php

class Model_Closing extends Model_Table {
	var $table= "closings";
	function init(){
		parent::init();

		$this->hasOne('Branch','branch_id');
		$this->addField('daily')->type('date');
		$this->addField('weekly')->type('date');
		$this->addField('monthly')->type('date');
		$this->addField('halfyearly')->type('date');
		$this->addField('yearly')->type('date');

		

		// $this->add('dynamic_model/Controller_AutoCreator');
	}
}