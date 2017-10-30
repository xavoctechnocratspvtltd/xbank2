<?php

class Model_Stock_Item extends Model_Table {
	var $table= "stock_items";
	function init(){
		parent::init();
 
		$this->hasOne('Branch','branch_id');
		// $this->addCondition('branch_id',$this->api->current_branch->id);
		$this->hasOne('Stock_Category','category_id')->sortable(true);
		
		$this->addField('name')->sortable(true);
		$this->addField('description')->type('text');
		$this->addField('is_consumable')->type('boolean')->defaultValue(false)->sortable(true);
		$this->addField('is_issueable')->type('boolean')->defaultValue(false)->sortable(true);
		$this->addField('is_fixedassets')->type('boolean')->defaultValue(false)->sortable(true);
		$this->addField('is_active')->type('boolean')->defaultValue(true)->sortable(true);
		
		$this->hasMany('Stock_Transaction','item_id');
		$this->hasMany('Stock_ContainerRowItemQty','item_id');
 		$this->addHook('beforeDelete',$this);
		$this->_dsql()->order('name','asc');

		// $this->add('dynamic_model/Controller_AutoCreator');
	}

 
	function createNew($name,$other_fields=array(),$form=nulll){
		if($this->loaded())
			throw $this->exception('This Function create New Items, So please pass Empty Object');
		
		if( ($other_fields['is_consumable'] + $other_fields['is_issueable'] + $other_fields['is_fixedassets'] ) > 1 or ($other_fields['is_consumable'] + $other_fields['is_issueable'] + $other_fields['is_fixedassets'] ) <= 0)
			throw $this->exception('select any one from (is_consumable, is_issueable, is_fixedassets)','ValidityCheck')->setField('is_fixedassets');

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
	
	function beforeDelete($model){
		if($this->ref('Stock_Transaction')->count()->getOne() > 0)
			throw $this->exception('Item ( '.$model['name'].' ) Cannot Delete');	
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


	function amount($as_on=null){
		if(!$as_on)
			$as_on=$this->api->now;
		return $this->getQty($as_on,$this->api->currentBranch->id)*$this->getAvgRate($as_on);
	}

	function getAvgRate($as_on){
		if(!$this->loaded())
			throw new \Exception("pass loaded model of item");
		if($as_on)
			$as_on=$this->api->now;

		//purchase and Opening
		$purchase_tra = $this->add('Model_Stock_Transaction');
		$purchase_tra->addCondition('item_id',$this->id);
		$purchase_tra->addCondition('created_at','<',$this->api->nextDate($as_on));
		$purchase_tra->addCondition('transaction_type',array('Purchase','Openning'));
		$purchase_tra_amount = ($purchase_tra->sum('rate')->getOne())?:0;
		$no_of_puchase=$purchase_tra->sum('qty')->getOne()?:0;
		
		// $no_of_puchase=$purchase_tra->count()->getOne()?:0;
		//Purchase Return
		$return_tra = $this->add('Model_Stock_Transaction');
		$return_tra->addCondition('item_id',$this->id);
		$return_tra->addCondition('created_at','<',$this->api->nextDate($as_on));
		$return_tra->addCondition('transaction_type','PurchaseReturn');
		$return_tra_amount = $return_tra->sum('rate')->getOne()?:0;
		$no_of_return = $return_tra->sum('qty')->getOne()?:0;
		// $no_of_return = $return_tra->count()->getOne()?:0;

		//Transfer In
		$transfer_in = $this->add('Model_Stock_Transaction');
		$transfer_in->addCondition('item_id',$this->id);
		$transfer_in->addCondition('created_at','<',$this->api->nextDate($as_on));
		$transfer_in->addCondition('transaction_type','Transfer');
		$transfer_in->addCondition('to_branch_id',$this->api->current_branch->id);
		$transfer_in->addCondition('branch_id','<>',$this->api->current_branch->id);
		$transfer_in_amount = $transfer_in->sum('rate')->getOne()?:0;
		$no_of_transfer_in = $transfer_in->sum('qty')->getOne()?:0;

		
		//Transfer Out
		$transfer_out = $this->add('Model_Stock_Transaction');
		$transfer_out->addCondition('item_id',$this->id);
		$transfer_out->addCondition('created_at','<',$this->api->nextDate($as_on));
		$transfer_out->addCondition('transaction_type','Transfer');
		$transfer_out->addCondition('branch_id',$this->api->current_branch->id);
		$transfer_out->addCondition('to_branch_id','<>',$this->api->current_branch->id);
		$transfer_out->tryLoadAny();
		$transfer_out_amount = $transfer_out->sum('rate')->getOne()?:0;
		$no_of_transfer_out = $transfer_out->sum('qty')->getOne()?:0;
		// throw new Exception($transfer_out_amount);

		//total amount
		$amount = ( $purchase_tra_amount + $transfer_in_amount ) - ( $return_tra_amount + $transfer_out_amount );
		
		//total qty 
		$qty =($no_of_puchase + $no_of_transfer_in) - ($no_of_return + $no_of_transfer_out);
		if(!$qty)
			$qty=1; 
			
		return $amount/$qty;
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

	function DeadQty($as_on=null){
		if(!$as_on)
			$as_on=$this->api->now;
		
		$dead_tra = $this->add('Model_Stock_Transaction');
		$dead_tra->addCondition('item_id',$this->id);
		$dead_tra->addCondition('created_at','<',$as_on);
		$dead_tra->addCondition('transaction_type','DeadSubmit');
		$dead_tra_qty = ($dead_tra->sum('qty')->getOne())?:0;
		return $dead_tra_qty;
	}


	function getOpeningQty($item_id=0,$branch_id=null){
		if(!$item_id)
			return 0;
		if(!$branch_id)
			$branch_id = $this->api->currentBranch->id;
		$opening_tra = $this->add('Model_Stock_Transaction');
		$opening_tra->addCondition('item_id',$item_id);
		$opening_tra->addCondition('transaction_type','Openning');
		$opening_tra->addCondition('branch_id',$branch_id);
				
		$opening_tra_qty = ($opening_tra->sum('qty')->getOne())?:0;
		return $opening_tra_qty;	
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
		elseif($dealer->loaded())
			$member = $dealer;
		else
			return 0;		
						
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
		
		//NEW UPDATION USED SUBMIT
		//ADD DUE TO USED SUBMIT TRANSACTION
		$transaction_stock_usedsubmit=$this->add('Model_Stock_Transaction');
		$transaction_stock_usedsubmit->addCondition('transaction_type','UsedSubmit');
		$transaction_stock_usedsubmit->addCondition('item_id',$this->id);
		$transaction_stock_usedsubmit->addCondition('member_id',$member->id);
		$transaction_stock_usedsubmit->addCondition('created_at','<',$this->api->nextDate($on_date));
		$transaction_stock_usedsubmit_qty=$transaction_stock_usedsubmit->sum('qty')->getOne();
		$usedsubmit_qty = $transaction_stock_usedsubmit_qty?:0;


		return ((($issue_qty-$submit_qty-$usedsubmit_qty) >= $qty)?:0);
	}

	function getQty($as_on,$branch_id=null){
		// if(!$this->loaded())
		// 	throw $this->exception('Pass Loaded Model Of Item');
					
		if(!$as_on) $as_on = $this->api->now;

		$container = $this->add('Model_Stock_Container');
		$container->addCondition('branch_id',$this->api->currentBranch->id);
		$used_container_id = [];
		foreach ($container as $cont) {
			if(preg_match('/used/',strtolower($cont['name'])))
				$used_container_id[$cont['id']] = $cont['id'];
		}

		// 'Openning'
		$openning_tra = $this->add('Model_Stock_Transaction');
		$openning_tra->addCondition('is_used_submit',0);
		$openning_tra->addCondition('item_id',$this->id);
		$openning_tra->addCondition('created_at','<',$as_on);
		$openning_tra->addCondition('transaction_type','Openning');
		if($branch_id)
			$openning_tra->addCondition('branch_id',$branch_id);

		$openning_tra_qty = ($openning_tra->sum('qty')->getOne())?:0;

		//Purchase
		$purchase_tra = $this->add('Model_Stock_Transaction');
		$purchase_tra->addCondition('is_used_submit',0);
		$purchase_tra->addCondition('item_id',$this->id);
		$purchase_tra->addCondition('created_at','<',$as_on);
		$purchase_tra->addCondition('transaction_type','Purchase');
		if($branch_id)
			$purchase_tra->addCondition('branch_id',$branch_id);
			
		$purchase_tra_qty = ($purchase_tra->sum('qty')->getOne())?:0;

		//'Submit'
		$submit_tra = $this->add('Model_Stock_Transaction');
		$submit_tra->addCondition('item_id',$this->id);
		$submit_tra->addCondition('is_used_submit',0);
		$submit_tra->addCondition('created_at','<',$as_on);
		$submit_tra->addCondition('transaction_type','Submit');
		if($branch_id)
			$submit_tra->addCondition('branch_id',$branch_id);

		$submit_tra_qty = ($submit_tra->sum('qty')->getOne())?:0;

		//Used Submit
		$used_submit_tra = $this->add('Model_Stock_Transaction');
		$used_submit_tra->addCondition('is_used_submit',0);
		$used_submit_tra->addCondition('item_id',$this->id);
		$used_submit_tra->addCondition('created_at','<',$as_on);
		$used_submit_tra->addCondition('transaction_type','UsedSubmit');
		if($branch_id)
			$used_submit_tra->addCondition('branch_id',$branch_id);

		$used_submit_tra_qty = ($used_submit_tra->sum('qty')->getOne())?:0;

		//'Transfer'
		$transfer_to_this_branch_tra = $this->add('Model_Stock_Transaction');
		// $transfer_to_this_branch_tra->addCondition('is_used_submit',0);
		$transfer_to_this_branch_tra->addCondition('item_id',$this->id);
		$transfer_to_this_branch_tra->addCondition('created_at','<',$as_on);
		$transfer_to_this_branch_tra->addCondition('to_branch_id',$this->api->currentBranch->id);
		$transfer_to_this_branch_tra->addCondition('transaction_type','Transfer');
		$transfer_to_this_branch_tra->addCondition('from_container_id','<>',$used_container_id);
		$transfer_to_this_branch_tra->addCondition('to_container_id','<>',$used_container_id);
		$transfer_to_this_branch_tra_qty = ($transfer_to_this_branch_tra->sum('qty')->getOne())?:0;

		//Transfer From
		$transfer_from_this_branch_tra = $this->add('Model_Stock_Transaction');
		// $transfer_from_this_branch_tra->addCondition('is_used_submit',0);
		$transfer_from_this_branch_tra->addCondition('item_id',$this->id);
		$transfer_from_this_branch_tra->addCondition('created_at','<',$as_on);
		$transfer_from_this_branch_tra->addCondition('branch_id',$this->api->currentBranch->id);
		$transfer_from_this_branch_tra->addCondition('transaction_type','Transfer');
		$transfer_from_this_branch_tra->addCondition('from_container_id','<>',$used_container_id);
		$transfer_from_this_branch_tra->addCondition('to_container_id','<>',$used_container_id);
		$transfer_from_this_branch_tra_qty = ($transfer_from_this_branch_tra->sum('qty')->getOne())?:0;

		//Issue
		$issue_tra = $this->add('Model_Stock_Transaction');
		$issue_tra->addCondition('is_used_submit',0);
		$issue_tra->addCondition('item_id',$this->id);
		$issue_tra->addCondition('branch_id',$this->api->currentBranch->id);
		$issue_tra->addCondition('created_at','<',$as_on);
		$issue_tra->addCondition('transaction_type','Issue');
		if($branch_id)
			$issue_tra->addCondition('branch_id',$branch_id);

		$issue_tra_qty = ($issue_tra->sum('qty')->getOne())?:0;

		//'DeadSubmit'
		$dead_tra = $this->add('Model_Stock_Transaction');
		$dead_tra->addCondition('is_used_submit',0);
		$dead_tra->addCondition('branch_id',$this->api->currentBranch->id);
		$dead_tra->addCondition('item_id',$this->id);
		$dead_tra->addCondition('created_at','<',$as_on);
		$dead_tra->addCondition('transaction_type','DeadSubmit');
		if($branch_id)
			$dead_tra->addCondition('branch_id',$branch_id);

		$dead_tra_qty = ($dead_tra->sum('qty')->getOne())?:0;

		//'DeadSold'
		$deadsold_tra = $this->add('Model_Stock_Transaction');
		$deadsold_tra->addCondition('is_used_submit',0);
		$deadsold_tra->addCondition('branch_id',$this->api->currentBranch->id);
		$deadsold_tra->addCondition('item_id',$this->id);
		$deadsold_tra->addCondition('created_at','<',$as_on);
		$deadsold_tra->addCondition('transaction_type','DeadSold');
		if($branch_id)
			$deadsold_tra->addCondition('branch_id',$branch_id);

		$deadsold_tra_qty = ($deadsold_tra->sum('qty')->getOne())?:0;

		//'Sold'	
		$sold_tra = $this->add('Model_Stock_Transaction');
		$sold_tra->addCondition('is_used_submit',0);
		$sold_tra->addCondition('branch_id',$this->api->currentBranch->id);
		$sold_tra->addCondition('item_id',$this->id);
		$sold_tra->addCondition('created_at','<',$as_on);
		$sold_tra->addCondition('transaction_type','Sold');
		if($branch_id)
			$sold_tra->addCondition('branch_id',$branch_id);

		$sold_tra_qty = ($sold_tra->sum('qty')->getOne())?:0;

		//'PurchaseReturn'
		$purchase_return_tra = $this->add('Model_Stock_Transaction');
		$purchase_return_tra->addCondition('is_used_submit',0);
		$purchase_tra->addCondition('branch_id',$this->api->currentBranch->id);
		$purchase_return_tra->addCondition('item_id',$this->id);
		$purchase_return_tra->addCondition('created_at','<',$as_on);
		$purchase_return_tra->addCondition('transaction_type','PurchaseReturn');
		if($branch_id)
			$purchase_return_tra->addCondition('branch_id',$branch_id);

		$purchase_return_tra_qty = ($purchase_return_tra->sum('qty')->getOne())?:0;

		//'Consume'
		$consume_tra = $this->add('Model_Stock_Transaction');
		$consume_tra->addCondition('is_used_submit',0);
		$consume_tra->addCondition('branch_id',$this->api->currentBranch->id);
		$consume_tra->addCondition('item_id',$this->id);
		$consume_tra->addCondition('created_at','<',$as_on);
		$consume_tra->addCondition('transaction_type','Consume');
		if($branch_id)
			$consume_tra->addCondition('branch_id',$branch_id);

		$consume_tra_qty = ($consume_tra->sum('qty')->getOne())?:0;
		// throw $this->exception("(($openning_tra_qty+$purchase_tra_qty+$submit_tra_qty+$transfer_to_this_branch_tra_qty)-($issue_tra_qty+$dead_tra_qty+$sold_tra_qty+$transfer_from_this_branch_tra_qty+$purchase_return_tra_qty));");
		// throw $this->exception($purchase_tra_qty);
		return (($openning_tra_qty+$purchase_tra_qty+$submit_tra_qty+$transfer_to_this_branch_tra_qty+$dead_tra_qty)-($issue_tra_qty+$sold_tra_qty+$transfer_from_this_branch_tra_qty+$purchase_return_tra_qty+$consume_tra_qty+$deadsold_tra_qty));

	}

	// function getItemQty($member,$item,$from_date,$to_date,$transaction_type,$member_type){	
		
	// 	$tra_model = $this->add('Model_Stock_Transaction',array('table_alias'=>'xt'));
	// 	$item_j=$tra_model->join('stock_items','item_id');
	// 	$tra_model->addCondition('branch_id',$this->api->currentBranch->id);
	// 	$tra_model->addCondition('created_at','<',$this->api->nextDate($from_date?:$this->api->now));
	// 	$tra_model->addCondition('transaction_type',$transaction_type);
	// 	$tra_model->addCondition('member_id',$member);
	// 	$tra_model->addCondition('item_id',$item);
	// 	$tra_model_qty = ($tra_model->sum('qty')->getOne())?:0;
		
	// 	throw new Exception("m".$member."item".$tra_model['item_id']);
	// 	return $tra_model_qty;
	// }

}