<?php
class Model_AgentGuarantor extends Model_Table {
	var $table= "agent_guarantors";
	function init(){
		parent::init();

		$this->hasOne('Agent','agent_id');

		$this->addField('name');
		$this->addField('father_husband_name');
		$this->addField('address');
		$this->addField('ph_no');
		$this->addField('occupation');


		$this->add('dynamic_model/Controller_AutoCreator');
	}
}