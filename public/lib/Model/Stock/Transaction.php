<?php

class Model_Stock_Transaction extends Model_Table {
	var $table= "stock_transactions";
	function init(){
		parent::init();
		
		$this->hasOne('Branch','branch_id');
		$this->addCondition('branch_id',$this->api->current_branch->id);
		$this->hasOne('Stock_Item','item_id');
		$this->hasOne('Stock_Party','party_id');
		
		$this->addField('qty');
		$this->addField('rate');
		$this->addField('amount');
		$this->addField('narration');
		$this->addField('created_at')->type('date')->defaultValue(date('Y-m-d'));
		$this->addField('issue_date');
		$this->addField('submit_date');
		$this->addField('transaction_type')->enum(array('Purchase','Issue','Consume','Submit','PurchaseReturn','Dead','Transfer','Openning','Sold'));
		
		$this->add('dynamic_model/Controller_AutoCreator');
	}

	function purchase(){

	}

	function purchaseReturn(){

	}

	function isPurchased(){

	}

	function issue(){

	}

	function consume(){

	}

	function submit(){

	}	

	function dead(){

	}

	function transfer(){

	}

	function openning(){

	}

	function sold(){

	}

	function remove(){
		
	}
	
}