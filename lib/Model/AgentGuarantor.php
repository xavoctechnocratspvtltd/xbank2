<?php
class Model_AgentGuarantor extends Model_Table {
	var $table= "agent_guarantors";
	function init(){
		parent::init();

		$this->hasOne('Member','member_id')->display(array('form'=>'autocomplete/Basic'));
		$this->hasOne('Agent','agent_id')->display(array('form'=>'autocomplete/Basic'));

		$this->hasMany('DocumentSubmitted','agentguarantor_id');

		$this->add('dynamic_model/Controller_AutoCreator');
	}
}