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
		$this->addField('transaction_type')->enum(array('Purchase','Issue','Consume','Submit','PurchaseReturn','Dead','Transfer','Move','Openning','Sold'));
		$this->addField('to_branch_id');
		
		$this->addHook('beforeDelete',$this);
		$this->addHook('afterInsert',$this);
		$this->addHook('afterSave',$this);
		

		$this->add('dynamic_model/Controller_AutoCreator');
	}

	function beforeDelete($transaction){
		
		switch ($transaction['transaction_type']) {
			case 'Purchase':
					$criq_model = $this->add('Model_Stock_ContainerRowItemQty');						
					$item = $this->add('Model_Stock_Item')->load($transaction['item_id']); 
					$criq_model->destroyStockFromGeneral($item,$transaction['qty']);
				break;

			case 'PurchaseReturn':
				$criq_model = $this->add('Model_Stock_ContainerRowItemQty');						
				$item = $this->add('Model_Stock_Item')->load($transaction['item_id']); 
				$criq_model->destroyStockFromGeneral($item,$transaction['qty']);
				break;		
			default:
						
				break;
		}
	}

	function afterInsert($transaction){
		switch ($transaction['transaction_type']) {
			case 'PurchaseReturn':
				$criq_model = $this->add('Model_Stock_ContainerRowItemQty');						
				$item = $this->add('Model_Stock_Item')->load($transaction['item_id']); 
				$criq_model->destroyStockFromGeneral($item,$transaction['qty']);
				break;	
			default:
						
				break;
		}

	}

	function afterSave($transaction){
		
	}

	function purchase($party,$item,$qty,$rate,$narration,$branch=null){
		
		if($this->loaded())
			throw $this->exception('Please Call On Empty Object','Transaction');
		if(!$party->loaded() and (!$party instanceof Model_Stock_Party))
			throw $this->exception('Please Pass loaded object of Party');
		
		if(!$item->loaded() and (!$item instanceof Model_Stock_Item))
			throw $this->exception('Please Pass loaded object of Item');

		if(!$branch)
			$branch=$this->api->currentBranch;
		 
		$this['party_id']=$party->id;
		$this['branch_id']=$branch->id;
		$this['item_id']=$item->id;
		$this['qty']=$qty;
		$this['rate']=$rate;
		$this['transaction_type']='Purchase';
		$this['narration']=$narration;
		$this->save();		
	}

	function purchaseReturn($party,$item,$qty,$rate,$narration,$branch=null){
		if($this->loaded())
			throw $this->exception('Please Call On Empty Object');
		if(!$branch)
				$branch=$this->api->currentBranch;
		if(!($party instanceof Model_Stock_Party) and !$party->loaded())
			throw $this->exception('Please pass loaded object of Party');
		
		if(!($item instanceof Model_Stock_Item) and !$item->loaded())
			throw $this->exception('Please pass loaded object of Item');
		if(!$item_purchased=$this->isPurchased($party,$item,$branch))
			throw $this->exception("Item ( ".$item['name']." ) is Not Purchase in Given Branch, Check Again");
		
		$criq_model = $this->add('Model_Stock_ContainerRowItemQty');	
		$item_in_general_stock = $criq_model->getItemFromGeneral($item);
		
		if( $qty > $item_in_general_stock['qty']){
			throw $this->exception('No Enough Item in General Conatiner');
		}	

		$tra=$this->add('Model_Stock_Transaction');
		$tra['party_id']=$party->id;
		$tra['branch_id']=$branch->id;
		$tra['item_id']=$item->id;
		$tra['qty']=$qty;
		$tra['rate']=$rate;//$item_purchased['rate'];
		$tra['transaction_type']='PurchaseReturn';
		$tra['narration']=$narration;
		$tra->save();

		// TODO after purchase return remove item from general stock

		// throw $this->exception(' Exception text', 'ValidityCheck')->setField('FieldName');
	}

	function isPurchased($party,$item,$branch=null){
		if(!$branch)
			$branch=$this->api->currentBranch;
		$tra=$this->add('Model_Stock_Transaction');
		$tra->addCondition('party_id',$party->id);
		$tra->addCondition('branch_id',$branch->id);
		$tra->addCondition('item_id',$item->id);
		$tra->addCondition('transaction_type','Purchase');
		$tra->tryLoadAny();
		if($tra->loaded())
			return $tra;
		else
			return false;

	}

	function transfer($from_branch_id,$container,$row,$item,$qty,$narration,$to_branch,$as_on=null){

		if($this->loaded())
			throw $this->exception('Please call on empty Object');
		if($container->loaded() and (!$container instanceof Model_Stock_Container))
			throw $this->exception('loaded object of Container Model');			
		if($row->loaded() and (!$row instanceof Model_Stock_row))
			throw $this->exception('loaded object of Row Model');
		if($item->loaded() and (!$item instanceof Model_Stock_Item))
			throw $this->exception('loaded object of Item Model');
		if(!$to_branch->loaded())
			throw $this->exception('Please pass loaded object of To Branch Model');		
		if(!$as_on)
			$as_on=$this->api->today;
		
		$criq_model = $this->add('Model_Stock_ContainerRowItemQty');
		if($criq_model->getItemQty($container,$row,$item) < $qty)
			throw $this->exception('This Is not availeble in such Qty', 'ValidityCheck')->setField('qty');
				
		$this['item_id']=$item->id;
		$this['branch_id']=$this->api->currentBranch->id;
		$this['to_branch_id']=$to_branch->id;
		$this['qty']=$qty;
		$this['narration']=$narration;
		$this['transaction_type']='Transfer';

		//todo itemrate
			//$this['rate']=$item['rate'];
		$this->save();
	}

	function issue(){

	}

	function consume(){

	}

	function submit(){

	}	

	function dead(){

	}

	function openning(){

	}

	function sold(){

	}

	function remove(){
		
	}
	
}