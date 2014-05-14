<?php
class Model_JointMember extends Model_Table {
	var $table= "jointmembers";
	function init(){
		parent::init();

		$this->hasOne('Account','account_id');
		$this->hasOne('Member','member_id');

		$this->add('dynamic_model/Controller_AutoCreator');
	}
}