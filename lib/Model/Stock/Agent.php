<?php

class Model_Stock_Agent extends Model_Stock_Member {

	function init(){
		parent::init();
		
		$this->addCondition('type','Agent');
				
	}
}