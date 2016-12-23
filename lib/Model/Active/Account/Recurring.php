<?php
class Model_Active_Account_Recurring extends Model_Account_Recurring{
	function init(){
		parent::init();
		$this->addCondition('ActiveStatus',true);
	}
}