<?php
class Model_Active_Account_Default extends Model_Account_Default{
	function init(){
		parent::init();
		$this->addCondition('ActiveStatus',true);
	}
}