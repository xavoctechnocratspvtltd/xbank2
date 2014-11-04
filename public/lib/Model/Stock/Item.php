<?php

class Model_Stock_Item extends Model_Table {
	var $table= "stock_items";
	function init(){
		parent::init();
 
		$this->hasOne('Branch','branch_id');
		$this->addCondition('branch_id',$this->api->current_branch->id);
		$this->hasOne('Stock_Category','category_id');
		
		$this->addField('name');
		$this->addField('description')->type('text');
		$this->addField('is_consumable')->type('boolean')->defaultValue(false);
		$this->addField('is_issueable')->type('boolean')->defaultValue(false);
		$this->addField('is_fixedassets')->type('boolean')->defaultValue(false);
		
		$this->hasMany('Stock_Transaction','item_id');
		$this->hasMany('Stock_ContainerRowItemQty','item_id');
 
		$this->_dsql()->order('name','asc');

		$this->add('dynamic_model/Controller_AutoCreator');
	}

 
	function createNew($name,$other_fields=array(),$form=nulll){
		if($this->loaded())
			throw $this->exception('This Function create New Items, So please pass Empty Object');
		
		$this['name']=$name;
		$this['category_id']=$other_fields['category_id'];
		$this['is_consumable']=$other_fields['is_consumable'];
		$this['is_issueable']=$other_fields['is_issueable'];
		$this['is_fixedassets']=$other_fields['is_fixedassets'];
		$this->save();
	}

	function remove(){
		if(!$this->loaded())
			throw $this->exception('Unable To determine The Recored to be delete ');
		$this->delete();
	}
	
	function isExistInRow($row){
		if(!$this->loaded())
			throw $this->exception('Item model is not loaded');
			
		if(!$row->loaded())
			throw $this->exception('Please pass loaded object of Row');

		$this->addCondition('row_id',$row->id);
		$this->tryLoadAny();
		if($this->loaded())
			return $this;
		else 
			return false;
	}

	function isExistInContainerRow($container,$row){
		if(!$this->loaded())
			throw $this->exception('Item model is not loaded');

		if( (!$row->loaded()) or (!$row instanceof Model_Stock_Row))
			throw $this->exception('Please pass loaded object of Row');

		if( (!$container->loaded()) or (!$container instanceof Model_Stock_Container))
			throw $this->exception('Please pass loaded object of Container');

		$criq_model = $this->add('Model_Stock_ContainerRowItemQty');
		$criq_model->addCondition('container_id',$container->id);
		$criq_model->addCondition('row_id',$row->id);
		$criq_model->addCondition('item_id',$this['id']);
		$criq_model->tryLoadAny();
		if(!$criq_model->loaded()){
			return false;
		}
		return $this;
	}

	function markConsumeable(){
		if(!$this->loaded())
			throw new Exception("Item Model is not Loaded");
		$this['is_consumable']=true;
		$this->save();
	}

	function markIssuable(){
		if(!$this->loaded())
			throw new Exception("Item Model is not Loaded");
		$this['is_issueable']=true;
		$this->save();
	}

	function markFixedAssest(){
		if(!$this->loaded())
			throw new Exception("Item Model is not Loaded");
		$this['is_fixedassets']=true;
		$this->save();
	}
	
	function issue(){
		// todo 
	}

	function submit(){
		// todo 
	}

	function dead(){
		// todo 
	}

	function transfer($item,$row,$container,$qty){
		$row_model = $this->add('Model_Stock_Row');
		$row_model->moveItem($item,$qty);
		$row_model->addItem($item,$qty);
	}

	function opening(){
		// todo
	}

	function sold(){
		// todo
	}

	function getAvgRate(){
		// todo
	}

	function getDetail(){
		// Container row item Qty detail

	}

	function getIssue(){
		// todo
	}

	function getConsume(){
		// todo
	}

	function isDead(){
		// todo
	}


	function isConsume(){
		// todo
	}

	function getDeadQty($qty,$as_on=null){
		if(!$as_on)
			$as_on=$this->api->now;
		
		$dead_tra = $this->add('Model_Stock_Transaction');
		$dead_tra->addCondition('item_id',$this->id);
		$dead_tra->addCondition('created_at','<',$this->api->nextDate($as_on));
		$dead_tra->addCondition('transaction_type','DeadSubmit');
		$dead_tra_qty = ($dead_tra->sum('qty')->getOne())?:0;
		return (($dead_tra_qty>=$qty)?:0);
	}	

	function getAllPurchase(){
		// todo
	}

	function canSubmit($qty,$on_date=null,$staff=null,$agent=null,$dealer=null){
		if(!$on_date) $on_date= $this->api->today;		

		if($staff->loaded() + $dealer->loaded() + $agent->loaded() > 1)
			throw $this->exception('Only One of Satff/Dealer/Agent is required', 'ValidityCheck')->setField('item');
		
		$member = null;
		if($staff->loaded())
			$member = $staff;
		elseif($agent->loaded())
			$member = $agent;
		else
			$member = $dealer;
		
						
		$transaction_stock_issue=$this->add('Model_Stock_Transaction');
		$transaction_stock_issue->addCondition('transaction_type','Issue');
		$transaction_stock_issue->addCondition('item_id',$this->id);
		$transaction_stock_issue->addCondition('member_id',$member->id);
		$transaction_stock_issue->addCondition('created_at','<',$this->api->nextDate($on_date));	
		$transaction_stock_issue_qty=$transaction_stock_issue->sum('qty')->getOne();
		$issue_qty = $transaction_stock_issue_qty?:0;
		
		
		$transaction_stock_submit=$this->add('Model_Stock_Transaction');
		$transaction_stock_submit->addCondition('transaction_type','Submit');
		$transaction_stock_submit->addCondition('item_id',$this->id);
		$transaction_stock_submit->addCondition('member_id',$member->id);
		$transaction_stock_submit->addCondition('created_at','<',$this->api->nextDate($on_date));

		$transaction_stock_submit_qty=$transaction_stock_submit->sum('qty')->getOne();
		$submit_qty = $transaction_stock_submit_qty?:0;

		return ((($issue_qty-$submit_qty) >= $qty)?:0);
	}


}