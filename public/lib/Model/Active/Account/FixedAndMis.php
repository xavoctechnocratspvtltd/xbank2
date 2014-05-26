<?php
class Model_Active_Account_FixedAndMis extends Model_Account_FixedAndMis{
	function init(){
		parent::init();
		$this->addCondition('ActiveStatus',true);
	}
}