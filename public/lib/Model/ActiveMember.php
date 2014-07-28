<?php
class Model_ActiveMember extends Model_Member {
	function init(){
		parent::init();

		$this->addCondition('is_active',true);

	}
}