<?php

class Model_Log extends Model_Table {
	var $table= "xLog";
	function init(){
		parent::init();

		$this->hasOne('Staff','staff_id');
		$this->hasOne('Account','account_id');
		$this->hasOne('Member','member_id');
		$this->hasOne('Agent','agent_id');
		$this->hasOne('Dealer','dealer_id');
		$this->hasOne('Branch','branch_id');
		$this->hasOne('Scheme','scheme_id');
		$this->hasOne('Transaction','transaction_id');
		$this->hasOne('TransactionRow','transaction_row_id');
		$this->hasOne('SubmittedDocument','documentsubmitted_id');

		$this->addField('created_at')->type('datetime')->defaultValue($this->api->now);

		$this->add('dynamic_model/Controller_AutoCreator');
	}

	function logFieldEdit($model,$record_id,$edit_what_field,$old_value,$new_value){

	}
}