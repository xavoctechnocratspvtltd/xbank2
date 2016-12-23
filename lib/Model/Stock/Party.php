<?php

class Model_Stock_Party extends Model_Stock_Member {

	function init(){
		parent::init();
		
		$this->addCondition('type','Party');
				
	}
}