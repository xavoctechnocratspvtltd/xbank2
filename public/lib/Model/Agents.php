<?php
class Model_Agents extends Model_Table {
	var $table= "xagents";
	function init(){
		parent::init();

		$this->hasOne('Member','member_id');
		$this->hasOne('Sponsor','sponsor_id');
		$this->hasOne('Tree','tree_id');
		$this->addField('ActiveStatus')->type('int');
		$this->addField('created_at')->type('datetime')->defaultValue($this->api->now);
		$this->addField('updated_at')->type('datetime')->defaultValue($this->api->now);
		$this->addField('AccountNumber');
		$this->addField('AgentCode');
		$this->addField('Path');
		$this->addField('LegCount')->type('int');
		$this->addField('Rank')->type('int');
		$this->addField('BusinessCreditPoints')->type('int');
		$this->addField('CumulativeBusinessCreditPoints')->type('int');
		// $this->addField('Rank_1_Count')->type('int');
		// $this->addField('Rank_2_Count')->type('int');
		// $this->addField('Rank_3_Count')->type('int');
		$this->hasMany('Gaurantor','gaurantor_id');
		//$this->add('dynamic_model/Controller_AutoCreator');
	}
}