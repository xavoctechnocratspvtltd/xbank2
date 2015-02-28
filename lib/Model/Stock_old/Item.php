<?php

class Model_Stock_Item extends Model_Table {
	var $table= "stock_items";
	function init(){
		parent::init();

		$this->hasOne('Stock_Category','category_id');
		$this->hasOne('Stock_Container','container_id');
		$this->hasOne('Stock_Row','row_id');
		$this->addField('name');
		$this->addField('description')->type('text');
		$this->addField('is_consumable')->type('boolean')->defaultValue(false);
		$this->addField('is_issueable')->type('boolean')->defaultValue(false);
		$this->addField('is_fixedassets')->type('boolean')->defaultValue(false);
		$this->hasMany('Stock_Transaction','item_id');
		$this->addHook('beforeDelete',$this);
		$this->addHook('beforeSave',$this);

		// $this->addExpression('container')->set(function($m,$q){
		// 	$cont_m= $m->add('Model_Stock_Container',array('table_alias'=>'xsc'));
		// 	$item_j = $cont_m->join('stock_rows.container_id')->join('stock_items.row_id');
		// 	$item_j->addField('xitem_id','id');
		// 	$cont_m->addCondition('xitem_id',$q->getField('id'));

		// 	return $cont_m->fieldQuery('name');
		// });


		
		$this->_dsql()->order('name','asc');

		$this->add('dynamic_model/Controller_AutoCreator');
	}

	function beforeSave(){

		$tmp = $this->add('Model_Stock_Item');
		$tmp->addCondition('name',$this['name']);
		$tmp->addCondition('category_id',$this['category_id']);

		if($this->loaded()){
			$tmp->addCondition('id','<>',$this->id);
		}

		$tmp->tryLoadAny();
		if($tmp->loaded())
			throw $this->exception('Name Already Exists','ValidityCheck')->setField('name');

		if($this['is_consumable'] and $this['is_issueable'])
							throw $this->exception("Can't Mark A single Item Issueable and Consumbale");

	}

	function isExistInRow($row){
		if(!$this->loaded())
			throw $this->exception('Please pass loaded object of Row');
			
		if(!$row->loaded())
			throw $this->exception('Please pass loaded object of Row');
		$this->addCondition('row_id',$row->id);
		$this->tryLoadAny();
		if($this->loaded())
			return $this;
		else 
			return false;
	}

	function createNew($name,$other_fields=array(),$form=nulll){
		if($this->loaded())
			throw $this->exception('This Function create New Items, So please pass Empty Object');
		

		$this['name']=$name;
		$this['category_id']=$other_fields['category_id'];
		$this['container_id']=$other_fields['container_id'];
		$this['row_id']=$other_fields['row_id'];
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

	function beforeDelete(){
		if($this->ref('Stock_Transaction')->count()->getOne() > 0)
			throw $this->exception('You can not Delete This Item, It contains Items');

	}


	function canSubmit($qty,$on_date=null){
		if(!$on_date) $on_date= $this->api->today;

		$transaction_stock_issue=$this->add('Model_Stock_Transaction');
		$transaction_stock_issue->addCondition('item_id',$this->id);
		$transaction_stock_issue->addCondition('created_at','<',$this->api->nextDate($on_date));
		$transaction_stock_issue->addCondition('transaction_type','Issue');
		$transaction_stock_issue_qty=$transaction_stock_issue->sum('qty')->getOne();
		$transaction_stock_issue_qty?:0;
		// throw new Exception($transaction_stock_issue_qty);
		$transaction_stock_submit=$this->add('Model_Stock_Transaction');
		$transaction_stock_submit->addCondition('item_id',$this->id);
		$transaction_stock_submit->addCondition('created_at','<',$this->api->nextDate($on_date));
		$transaction_stock_submit->addCondition('transaction_type','Submit');
		$transaction_stock_submit_qty=$transaction_stock_submit->sum('qty')->getOne();
		$transaction_stock_submit_qty?:0;
		// throw $this->exception(($transaction_stock_issue_qty-$transaction_stock_submit_qty) >= $qty, 'ValidityCheck')->setField('FieldName');
		// throw $this->exception(($transaction_stock_issue_qty-$transaction_stock_submit_qty) >= $qty);

		return ((($transaction_stock_issue_qty-$transaction_stock_submit_qty) >= $qty)?:0);
	}


	
	function markConsumeable(){

	}

	function markIssuable(){

	}

	function markFixedAssest(){

	}

	function purchase(){

	}

	function purchaseReturn(){

	}

	function issue(){

	}

	function submit(){

	}

	function dead(){

	}

	function transfer(){

	}


	function opening(){


	}

	function sold(){
		
	}

	

	function getQty($as_on=null){
		if(!$as_on) $as_on = $this->api->now;

		$openning_tra = $this->add('Model_Stock_Transaction');
		$openning_tra->addCondition('item_id',$this->id);
		$openning_tra->addCondition('created_at','<',$this->api->nextDate($as_on));
		$openning_tra->addCondition('transaction_type','Openning');
		$openning_tra_qty = ($openning_tra->sum('qty')->getOne())?:0;

		$purchase_tra = $this->add('Model_Stock_Transaction');
		$purchase_tra->addCondition('item_id',$this->id);
		$purchase_tra->addCondition('created_at','<',$this->api->nextDate($as_on));
		$purchase_tra->addCondition('transaction_type','Purchase');
		$purchase_tra_qty = ($purchase_tra->sum('qty')->getOne())?:0;

		$submit_tra = $this->add('Model_Stock_Transaction');
		$submit_tra->addCondition('item_id',$this->id);
		$submit_tra->addCondition('created_at','<',$this->api->nextDate($as_on));
		$submit_tra->addCondition('transaction_type','Submit');
		$submit_tra_qty = ($submit_tra->sum('qty')->getOne())?:0;


		$transfer_to_this_branch_tra = $this->add('Model_Stock_Transaction');
		$transfer_to_this_branch_tra->addCondition('item_id',$this->id);
		$transfer_to_this_branch_tra->addCondition('created_at','>',$this->api->nextDate($as_on));
		$transfer_to_this_branch_tra->addCondition('to_branch_id','<',$this->api->currentBranch->id);
		$transfer_to_this_branch_tra->addCondition('transaction_type','Transfer');
		$transfer_to_this_branch_tra_qty = ($transfer_to_this_branch_tra->sum('qty')->getOne())?:0;
		
		$transfer_from_this_branch_tra = $this->add('Model_Stock_Transaction');
		$transfer_from_this_branch_tra->addCondition('item_id',$this->id);
		$transfer_from_this_branch_tra->addCondition('created_at','<',$this->api->nextDate($as_on));
		$transfer_from_this_branch_tra->addCondition('branch_id',$this->api->currentBranch->id);
		$transfer_from_this_branch_tra->addCondition('transaction_type','Transfer');
		$transfer_from_this_branch_tra_qty = ($transfer_from_this_branch_tra->sum('qty')->getOne())?:0;		

		$issue_tra = $this->add('Model_Stock_Transaction');
		$issue_tra->addCondition('item_id',$this->id);
		$issue_tra->addCondition('created_at','<',$this->api->nextDate($as_on));
		$issue_tra->addCondition('transaction_type','Issue');
		$issue_tra_qty = ($issue_tra->sum('qty')->getOne())?:0;

		$dead_tra = $this->add('Model_Stock_Transaction');
		$dead_tra->addCondition('item_id',$this->id);
		$dead_tra->addCondition('created_at','<',$this->api->nextDate($as_on));
		$dead_tra->addCondition('transaction_type','Dead');
		$dead_tra_qty = ($dead_tra->sum('qty')->getOne())?:0;

		$sold_tra = $this->add('Model_Stock_Transaction');
		$sold_tra->addCondition('item_id',$this->id);
		$sold_tra->addCondition('created_at','<',$this->api->nextDate($as_on));
		$sold_tra->addCondition('transaction_type','Sold');
		$sold_tra_qty = ($sold_tra->sum('qty')->getOne())?:0;

		$purchase_return_tra = $this->add('Model_Stock_Transaction');
		$purchase_return_tra->addCondition('item_id',$this->id);
		$purchase_return_tra->addCondition('created_at','<',$this->api->nextDate($as_on));
		$purchase_return_tra->addCondition('transaction_type','PurchaseReturn');
		$purchase_return_tra_qty = ($purchase_return_tra->sum('qty')->getOne())?:0;

		$consume_tra = $this->add('Model_Stock_Transaction');
		$consume_tra->addCondition('item_id',$this->id);
		$consume_tra->addCondition('created_at','<',$this->api->nextDate($as_on));
		$consume_tra->addCondition('transaction_type','Consume');
		$consume_tra_qty = ($consume_tra->sum('qty')->getOne())?:0;
		// throw $this->exception("(($openning_tra_qty+$purchase_tra_qty+$submit_tra_qty+$transfer_to_this_branch_tra_qty)-($issue_tra_qty+$dead_tra_qty+$sold_tra_qty+$transfer_from_this_branch_tra_qty+$purchase_return_tra_qty));");
		// throw $this->exception($purchase_tra_qty);
		return (($openning_tra_qty+$purchase_tra_qty+$submit_tra_qty+$transfer_to_this_branch_tra_qty)-($issue_tra_qty+$dead_tra_qty+$sold_tra_qty+$transfer_from_this_branch_tra_qty+$purchase_return_tra_qty+$consume_tra_qty));

	}

	function getIssueConsume($as_on=null){
		if(!$as_on)
			$as_on=$this->api->now;
		$issue_tra = $this->add('Model_Stock_Transaction');
		$issue_tra->addCondition('item_id',$this->id);
		$issue_tra->addCondition('created_at','<',$this->api->nextDate($as_on));
		$issue_tra->addCondition('transaction_type','Issue');
		$issue_tra_qty = ($issue_tra->sum('qty')->getOne())?:0;


		$consume_tra = $this->add('Model_Stock_Transaction');
		$consume_tra->addCondition('item_id',$this->id);
		$consume_tra->addCondition('created_at','<',$this->api->nextDate($as_on));
		$consume_tra->addCondition('transaction_type','Consume');
		$consume_tra_qty = ($consume_tra->sum('qty')->getOne())?:0;

		return (($issue_tra_qty-$consume_tra_qty)?:0);

	}

	function getAvgRate($as_on=null){
		if($as_on)
			$as_on=$this->api->now;
		$purchase_tra = $this->add('Model_Stock_Transaction');
		$purchase_tra->addCondition('item_id',$this->id);
		$purchase_tra->addCondition('created_at','<',$this->api->nextDate($this->api->now));
		$purchase_tra->addCondition('transaction_type','Purchase');
		$purchase_tra_qty = ($purchase_tra->sum('rate')->getOne())?:0;
		$no_of_puchase=$purchase_tra->count()->getOne()?:1;
		// throw new Exception($purchase_tra_qty/$no_of_puchase, 1);
		
		return $purchase_tra_qty/$no_of_puchase;
	}


	function amount($as_on=null){
		if(!$as_on)
			$as_on=$this->api->now;
		return $this->getQty($as_on)*$this->getAvgRate($as_on);
	}
	function getDeadQty($qty,$as_on=null){
		if(!$as_on)
			$as_on=$this->api->now;
		$dead_tra = $this->add('Model_Stock_Transaction');
		$dead_tra->addCondition('item_id',$this->id);
		$dead_tra->addCondition('created_at','<',$this->api->nextDate($as_on));
		$dead_tra->addCondition('transaction_type','Dead');
		$dead_tra_qty = ($dead_tra->sum('qty')->getOne())?:0;
		return (($dead_tra_qty>=$qty)?:0);
	}
}