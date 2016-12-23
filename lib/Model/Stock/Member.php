<?php

class Model_Stock_Member extends Model_Table {
	var $table= "stock_members";

	function init(){
		parent::init();
		
		$this->hasOne('Branch','branch_id');
		$this->addCondition('branch_id',$this->api->current_branch->id);
		
		$this->addField('name')->mandatory(true)->sortable(true);
		$this->addField('address')->type('text');
		$this->addField('ph_no')->mandatory(true);
		$this->addField('type')->mandatory(true)->enum(array('Agent', 'Dealer', 'Staff', 'Supplier'))->sortable(true);	
		$this->addField('is_active')->type('boolean')->defaultValue(true)->sortable(true);
		
		$this->addHook('beforeSave',$this);
		$this->hasMany('Model_Stock_Transaction','member_id');
		// $this->add('dynamic_model/Controller_AutoCreator');
	}

	function beforeSave($m){
		if(strlen($m['ph_no']) < 10)
			throw $this->exception("Minimum 10 Digit Number","ValidityCheck")->setField('ph_no');
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
		// $submit_tra->addCondition('transaction_type','Submit');
		$submit_tra->addCondition('transaction_type',array('Submit','DeadSubmit','UsedSubmit'));
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

	function getSupplierOpeningQty($member,$item,$as_on){
		if(!$as_on)	
			$as_on = '1970-01-01';
		if(!$member)
			throw new \Exception("must pass supplier");
				
		$purchase_tra = $this->add('Model_Stock_Transaction');
		if($item)
			$purchase_tra->addCondition('item_id',$item);
		$purchase_tra->addCondition('branch_id',$this->api->currentBranch->id);
		$purchase_tra->addCondition('created_at','<',$as_on);
		$purchase_tra->addCondition('member_id',$member);
		$return_tra = $purchase_tra->tryLoadAny();

		$purchase_tra->addCondition('transaction_type','Purchase');
		$purchase_tra_qty = ($purchase_tra->sum('qty')->getOne())?:0;
		
		$return_tra->addCondition('transaction_type','PurchaseReturn');
		$return_tra->tryLoadAny();
		$return_tra_qty = ($return_tra->sum('qty')->getOne())?:0;
		
		return( $purchase_tra_qty - $return_tra_qty );			
	}		

	function getSupplierPurchaseQty($supplier,$item,$from_date,$to_date){
		if(!$from_date)
			$from_date = '1970-01-01';

		$purchase_tra = $this->add('Model_Stock_Transaction');
		$purchase_tra->addCondition('transaction_type','Purchase');
		$purchase_tra->addCondition('member_id',$supplier);
		if($from_date)
			$purchase_tra->addCondition('created_at','>=',$from_date);
		if($to_date)
			$purchase_tra->addCondition('created_at','<=',$to_date);
		$purchase_tra->addCondition('item_id',$item);
		$purchase_tra->tryLoadAny();
		return ($purchase_tra->sum('qty')->getOne())?:0;

	}

	function getSupplierPurchaseReturnQty($supplier,$item,$from_date,$to_date){
		
		$purchase_return_tra = $this->add('Model_Stock_Transaction');
		$purchase_return_tra->addCondition('transaction_type','PurchaseReturn');
		$purchase_return_tra->addCondition('member_id',$supplier);
		if($from_date)
			$purchase_return_tra->addCondition('created_at','>=',$from_date);
		if($to_date)
			$purchase_return_tra->addCondition('created_at','<=',$to_date);
		$purchase_return_tra->addCondition('item_id',$item);
		$purchase_return_tra->tryLoadAny();
		return ($purchase_return_tra->sum('qty')->getOne())?:0;
	}

	function getQty($member,$item,$as_on,$to_date,$transaction_type){
		if(!$as_on)	
			$as_on = '1970-01-01';
		if(!$to_date)
			$this->api->now;	
		if(!$member)
			throw new \Exception("must pass supplier");
				
		$tra = $this->add('Model_Stock_Transaction');
		if($item)
			$tra->addCondition('item_id',$item);
		$tra->addCondition('created_at','>',$as_on);
		$tra->addCondition('member_id',$member);
		$tra->addCondition('branch_id',$this->api->currentBranch->id);
		$tra->addCondition('transaction_type',$transaction_type);
		// $tra->addCondition('created_at','<',$to_date);
		$tra_qty = ($tra->sum('qty')->getOne())?:0;
		return($tra_qty);				
	}

}