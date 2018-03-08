<?php
class Model_Active_Account_DDS2 extends Model_Account_DDS2{
	function init(){
		parent::init();
		$this->addCondition('ActiveStatus',true);
	}
}