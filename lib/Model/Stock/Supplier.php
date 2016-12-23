<?php

class Model_Stock_Supplier extends Model_Stock_Member {

	function init(){
		parent::init();
		
		$this->addCondition('type','Supplier');
				
	}
}