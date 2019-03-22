<?php

class Model_NocLog extends Model_Table {
	public $table='noclog';
	public $title_field = "account";

	function init(){
		parent::init();

		$this->hasOne('Account_Loan','accounts_id')->display(array('form'=>'autocomplete/Basic'))->caption('NOC Account')->mandatory(true);
		$this->hasOne('Branch','from_branch_id')->defaultValue($this->app->current_branch->id)->system(true);
		$this->hasOne('Branch','to_branch_id');
		$this->hasOne('Staff','created_by_id')->defaultValue($this->app->current_staff->id)->system(true);
		$this->hasOne('Staff','received_by_id');
		$this->hasOne('Staff','dispatch_by_id');
		$this->hasOne('Staff','return_by_id');
		$this->hasOne('Staff','return_received_by_id');
		
		$this->addField('noc_letter_received_on')->type('datetime');
		$this->addField('send_at')->type('datetime')->defaultValue($this->app->now)->system(true)->sortable(true);
		$this->addField('send_narration')->type('text');

		$this->addField('received_at')->type('datetime');
		$this->addField('received_narration')->type('text');
		
		$this->addField('is_dispatch_to_customer')->type('boolean')->defaultValue(false);
		$this->addField('dispatch_narration')->type('text');
		$this->addField('dispatch_at')->type('datetime');

		$this->addField('is_return')->type('boolean')->defaultValue(0);
		$this->addField('return_at')->type('datetime');
		$this->addField('return_narration')->type('text');

		$this->addField('return_received_narration')->type('text');

		$this->addField('is_noc_not_made')->type('boolean')->defaultValue(false);
		$this->addField('noc_not_made_due_to')->type('text');
		$this->addField('is_noc_hold')->type('boolean')->defaultValue(false);
		$this->addField('noc_hold_due_to')->type('text');

		$this->addHook('beforeSave',$this);
		$this->add('dynamic_model/Controller_AutoCreator');
	}

	function beforeSave(){
		if($this['is_dispatch_to_customer'] && !$this['dispatch_at']) $this['dispatch_at'] = $this->app->now;
	}


	// return an array of noc
	function getSendNocIds(){
		$ids = $this->add('Model_NocLog')
				->addCondition('is_return',false)
				->_dsql()->del('fields')->field('accounts_id')->getAll();
		if(!count($ids)) return 0;
		
		return iterator_to_array(new \RecursiveIteratorIterator(new \RecursiveArrayIterator($ids)),false);
	}

}