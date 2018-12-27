<?php

class Model_StockNew_Dealer extends Model_StockNew_Member {

	function init(){
		parent::init();
		
		$this->addCondition('type','Dealer');
				
	}
}