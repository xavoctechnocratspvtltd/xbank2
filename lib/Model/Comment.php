<?php

class Model_Comment extends Model_Table{
	public $table="Comment";
	function init(){
		parent::init();
		$this->hasOne('Member','member_id');
		$this->hasOne('Account','account_id');
		$this->addField('narration')->type('text');
		$this->addField('created_at')->type('datetime');
		$this->addField('updated_at')->type('datetime');
		$this->add('dynamic_model/Controller_AutoCreator');

	}

	function createNew($narration,$member=null,$account=null){
		
		if($this->loaded())
			throw new Exception("Please call on empty model");
		if($member)
			$this['member_id']=$member->id;
		if($account)
			$this['account_id']=$account->id;
		$this['narration']=$narration;
		$this['created_at']=$this->api->now;
		$this['updated_at']=$this->api->now;
		$this->save();
			
	}
}