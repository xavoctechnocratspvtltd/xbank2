<?php
class Model_Premium extends Model_Table {
	var $table= "premiums";
	function init(){
		parent::init();


		$this->hasOne('Account','account_id');
		$this->addField('Amount');
		$this->addField('Paid')->type('boolean');
		$this->addField('Skipped')->type('boolean');
		$this->addField('created_at')->type('datetime')->defaultValue($this->api->now);
		$this->addField('updated_at')->type('datetime')->defaultValue($this->api->now);
		$this->addField('PaidOn')->type('date');
		$this->addField('AgentCommissionSend')->type('boolean');
		$this->addField('AgentCommissionPercentage')->type('money');
		$this->addField('DueDate')->type('date');
		//$this->add('dynamic_model/Controller_AutoCreator');
	}
}