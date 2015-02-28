<?php
class Model_AccountGuarantor extends Model_Table {
	var $table= "account_guarantors";
	function init(){
		parent::init();

		$this->hasOne('Account','account_id');
		$this->hasOne('Member','member_id')->display(array('form'=>'autocomplete/Basic'));

		$this->addField('created_at')->type('date')->defaultValue($this->api->today);

		// $this->addField('name');
		// $this->addField('father_husband_name');
		// $this->addField('address');
		// $this->addField('ph_no');
		// $this->addField('occupation');


		$this->add('dynamic_model/Controller_AutoCreator');
	}
}