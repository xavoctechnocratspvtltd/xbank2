<?php

class Model_Active_Account extends Model_Account {
	function init(){
		parent::init();

		$this->addCondition('ActiveStatus',true);
	}
}