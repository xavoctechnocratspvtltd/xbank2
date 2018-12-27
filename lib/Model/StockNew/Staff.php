<?php

class Model_StockNew_Staff extends Model_StockNew_Member {

	function init(){
		parent::init();
		
		$this->addCondition('type','Staff');
				
	}
}