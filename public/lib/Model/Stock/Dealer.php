<?php

class Model_Stock_Dealer extends Model_Stock_Member {

	function init(){
		parent::init();
		
		$this->addCondition('type','Dealer');
				
	}
}