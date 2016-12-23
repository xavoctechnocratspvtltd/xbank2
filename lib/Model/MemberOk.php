<?php

class Model_MemberOk extends Model_Member {
	function init(){
		parent::init();
		
		$this->addOkConditions();
		//$this->add('dynamic_model/Controller_AutoCreator');
	}
}