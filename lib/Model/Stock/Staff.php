<?php

class Model_Stock_Staff extends Model_Stock_Member {

	function init(){
		parent::init();
		
		$this->addCondition('type','Staff');
				
	}
}