<?php
class Model_Active_Account_CC extends Model_Account_CC{
	function init(){
		parent::init();
		$this->addCondition('ActiveStatus',true);
	}
}