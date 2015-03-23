<?php
class Model_Agent extends Model_Table {
	var $table= "agents";

	function init(){
		parent::init();

		$this->hasOne('Member','member_id')->display(array('form'=>'autocomplete/Basic'));
		$this->hasOne('Agent','sponsor_id');
		$this->hasOne('Account_SavingAndCurrent','account_id')->caption('Saving Account')->display(array('form'=>'autocomplete/Basic'));;
		$this->hasOne('Cadre','cadre_id');
		// $this->hasOne('Tree','tree_id');
		$this->addField('ActiveStatus')->type('boolean')->defaultValue(true);
		$this->addField('created_at')->type('datetime')->defaultValue($this->api->now)->system(true);
		$this->addField('updated_at')->type('datetime')->defaultValue($this->api->now)->system(true);
		$this->addField('AgentCode')->system(true);
		$this->addField('Path')->system(true);
		$this->addField('LegCount')->type('int')->system(true);
		$this->addField('Rank')->type('int')->system(true);
		$this->addField('BusinessCreditPoints')->type('int')->system(true);
		$this->addField('CumulativeBusinessCreditPoints')->type('int')->system(true);
		// $this->addField('Rank_1_Count')->type('int');
		// $this->addField('Rank_2_Count')->type('int');
		// $this->addField('Rank_3_Count')->type('int');
		$this->hasMany('AgentGuarantor','agent_id');
		$this->hasMany('DocumentSubmitted','agent_id');
		

		$this->addExpression('name')->set(function($m,$q){
			return $m->refSQL('member_id')->fieldQuery('name');
		});

		$this->addHook('beforeDelete',$this);
		// $this->addHook('beforeSave',$this);

		// $this->add('dynamic_model/Controller_AutoCreator');
	}

	function beforeDelete(){
		throw new Exception("Agent Delete Hook ????", 1);
		
	}

	// function beforeDelete(){
	// 	$agent=$this->add('Model_Agent');

	// 	$agent_member_j=$agent->join('members','member_id');
	// 	$agent_member_j->hasMany('Account')
		
	// }
}