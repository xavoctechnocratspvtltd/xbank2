<?php
class Model_DocumentSubmitted extends Model_Table {
	var $table= "documents_submitted";
	function init(){
		parent::init();

		$this->hasOne('Document','documents_id');
		$this->hasOne('Account','accounts_id');
		$this->addField('Description');
		//$this->add('dynamic_model/Controller_AutoCreator');
	}
}