<?php
class Model_JointMember extends Model_Table {
	var $table= "jointmembers";
	function init(){
		parent::init();

		$this->hasOne('Account','account_id')->display(['form'=>'autocomplete/Basic']);
		$this->hasOne('Member','member_id','name')->display(['form'=>'autocomplete/Basic']);

		$this->add('dynamic_model/Controller_AutoCreator');
	}

	function delete($forced){
		return parent::delete($forced);
	}
}