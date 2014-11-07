<?php

class Model_Stock_Member extends Model_Table {
	var $table= "stock_members";

	function init(){
		parent::init();
		
		$this->hasOne('Branch','branch_id');
		$this->addCondition('branch_id',$this->api->current_branch->id);
		
		$this->addField('name');
		$this->addField('address');
		$this->addField('ph_no');
		$this->addField('type')->enum(array('Agent', 'Dealer', 'Party', 'Staff', 'Supplier'));	
		$this->addField('is_active')->type('boolean')->defaultValue(true);
		
		$this->hasMany('Model_Stock_Transaction','member_id');
		$this->add('dynamic_model/Controller_AutoCreator');
	}

	function createNew($name,$other_fields=array(),$form=null){

		if($this->loaded())
			throw $this->exception('Please call on loaded Object');
		$this['name']=$name;
		$this['address']=$other_fields['address'];
		$this['ph_no']=$other_fields['ph_no'];
		$this['type']=$other_fields['type'];
		$this['is_active']=$other_fields['is_active'];
		$this->save();
	}
	
	function getOpeningQty($member,$item,$as_on){

		if(!$as_on)	
			$as_on = '1970-01-01';

		if(!$member)
			throw new \Exception("must pass member");
				
		$submit_tra = $this->add('Model_Stock_Transaction');
		$submit_tra->addCondition('item_id',$item);
		$submit_tra->addCondition('created_at','<',$as_on);
		$submit_tra->addCondition('member_id',$member);
		$submit_tra->addCondition('transaction_type','Submit');
		$submit_tra_qty = ($submit_tra->sum('qty')->getOne())?:0;

		$issue_tra = $this->add('Model_Stock_Transaction');
		$issue_tra->addCondition('item_id',$item);
		$issue_tra->addCondition('branch_id',$this->api->currentBranch->id);
		$issue_tra->addCondition('created_at','<',$as_on);
		$issue_tra->addCondition('member_id',$member);
		$issue_tra->addCondition('transaction_type','Issue');
		$issue_tra->tryLoadAny();
		$issue_tra_qty = ($issue_tra->sum('qty')->getOne())?:0;
		
		return($issue_tra_qty - $submit_tra_qty);
		
	}		

}