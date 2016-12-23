<?php
class Model_Active_Account_SavingAndCurrent extends Model_Account_SavingAndCurrent{
	function init(){
		parent::init();
		$this->addCondition('ActiveStatus',true);
	}
}