<?php

class Model_MoAgentAssociation extends Model_Table {
	public $table = 'mo_agent_association';

	function init(){
		parent::init();

		$this->hasOne('Mo','mo_id');
		$this->hasOne('Agent','agent_id');
		$this->addField('from_date')->type('datetime')->defaultValue($this->app->now);
		$this->addField('_to_date')->type('datetime');

		$this->addExpression('to_date')->set(function($m,$q){
			return $q->expr('IFNULL([0],"[1]")',[$m->getElement('_to_date'),$this->app->now]);
		});

		$this->add('dynamic_model/Controller_AutoCreator');
	}
}