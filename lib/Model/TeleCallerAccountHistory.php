<?php

class Model_TeleCallerAccountHistory extends Model_Table {
	public $table = 'telecaller_account_history';

	function init(){
		parent::init();

		$this->hasOne('TeleCaller','telecaller_id')->display(['form'=>'autocomplete/Basic'])->sortable(true);
		$this->hasOne('Account','account_id')->display(['form'=>'autocomplete/Basic'])->sortable(true);
		$this->addField('from_date')->type('datetime')->defaultValue($this->app->now);
		$this->addField('final_to_date')->type('datetime')->caption('Orig To Date')->sortable(true);

		$this->addExpression('to_date')->set(function($m,$q){
			return $q->expr('IFNULL([0],"[1]")',[$m->getElement('final_to_date'),$this->app->now]);
		})->type('datetime')->sortable(true);

		$this->setOrder('from_date','desc');

		$this->add('dynamic_model/Controller_AutoCreator');
	}
}