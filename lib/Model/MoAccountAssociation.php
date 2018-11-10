<?php

class Model_MoAccountAssociation extends Model_Table {
	public $table = 'mo_account_association';

	function init(){
		parent::init();

		$this->hasOne('Mo','mo_id')->display(['form'=>'autocomplete/Basic'])->sortable(true);
		$this->hasOne('Account','account_id')->display(['form'=>'autocomplete/Basic'])->sortable(true);
		$this->addField('from_date')->type('datetime')->defaultValue($this->app->now);
		$this->addField('_to_date')->type('datetime')->caption('Orig To Date');

		$this->addExpression('to_date')->set(function($m,$q){
			return $q->expr('IFNULL([0],"[1]")',[$m->getElement('_to_date'),$this->app->now]);
		});

		$this->add('dynamic_model/Controller_AutoCreator');
	}
}