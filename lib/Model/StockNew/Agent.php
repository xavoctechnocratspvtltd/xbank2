<?php

class Model_StockNew_Agent extends Model_StockNew_Member {

	function init(){
		parent::init();
		
		$this->addCondition('type','Agent');
				
	}
}