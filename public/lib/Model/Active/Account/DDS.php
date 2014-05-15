<?php
class Model_Active_Account_DDS extends Model_Account_DDS{
	function init(){
		parent::init();
		$this->addCondition('ActiveStatus',true);
	}
}