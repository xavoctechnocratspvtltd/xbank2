<?php
class Model_DocumentSubmitted extends Model_Table {
	var $table= "documents_submitted";
	function init(){
		parent::init();

		$this->hasOne('Document','documents_id');
		$this->hasOne('Account','accounts_id');
		$this->hasOne('Member','member_id');
		$this->hasOne('Agent','agent_id');
		$this->hasOne('AgentGuarantor','agentguarantor_id');
		$this->hasOne('DSA','dsa_id');
		$this->hasOne('DSAGuarantor','dsaguarantor_id');

		$this->addField('Description');
		
		$this->addField('submitted_on')->type('date')->defaultValue($this->api->today);
		$this->add('dynamic_model/Controller_AutoCreator');
	}
}