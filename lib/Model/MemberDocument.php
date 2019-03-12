<?php
class Model_MemberDocument extends Model_Table {
	var $table= "member_documnets";

	function init(){
		parent::init();

		$this->hasOne('Account','accounts_id')->display(array('form'=>'autocomplete/Basic'));
		$this->hasOne('Member','member_id')->display(array('form'=>'Member'));

		$this->addField('name')->defaultValue('Form 60\61')->system(true);
		$this->add('filestore/Field_Image','doc_image_id')->type('image');//->display(array('grid'=>'picture'));//->mandatory(true);
		$this->addField('description')->type('text');
		$this->addField('submitted_on')->type('datetime')->defaultValue($this->api->today);
		
		// $this->add('dynamic_model/Controller_AutoCreator');
	}

}