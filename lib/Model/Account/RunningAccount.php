<?php

class Model_RunningAccount extends Model_Active_Account {
	function init(){
		parent::init();

		$this->addCondition('MaturedStatus',true);
		//$this->add('dynamic_model/Controller_AutoCreator');
	}
}