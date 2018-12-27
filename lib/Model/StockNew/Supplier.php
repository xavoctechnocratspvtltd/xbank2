<?php

class Model_StockNew_Supplier extends Model_StockNew_Member {

	function init(){
		parent::init();
		
		$this->addCondition('type','Supplier');
				
	}
}