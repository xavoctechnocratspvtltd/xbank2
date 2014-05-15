<?php
class Model_Active_AccountFixedAndMis extends Model_AccountFixedAndMis{
	function init(){
		parent::init();
		$this->addCondition('ActiveStatus',true);
	}
}