
<?php

class Model_Stock_Transaction extends Model_Table {
	var $table= "stock_transactions";
	function init(){
		parent::init();
		
		$this->hasOne('Branch','branch_id');
		// $this->addCondition('branch_id',$this->api->current_branch->id);
		$this->hasOne('Stock_Item','item_id');
		$m = $this->hasOne('Model_Stock_Member','member_id');
		$m->defaultValue(0);
		$this->hasOne('Model_Stock_Container','from_container_id')->defaultValue(0)->sortable(true);
		$this->hasOne('Model_Stock_Container','to_container_id')->defaultValue(0)->sortable(true);
		$this->hasOne('Model_Stock_Row','to_row_id')->defaultValue(0)->sortable(true);
		$this->hasOne('Model_Stock_Row','from_row_id')->defaultValue(0)->sortable(true);

		$this->addField('qty')->defaultValue(0);
		$this->addField('rate')->type('money')->defaultValue(0); 
		$this->addField('amount')->type('money')->defaultValue(0);
		$this->addField('narration');
		$this->addField('created_at')->type('date')->defaultValue($this->api->today);
		$this->addField('issue_date');
		$this->addField('submit_date');
		$this->addField('transaction_type')->enum(array('Purchase','Issue','Consume','Submit','PurchaseReturn','DeadSubmit','Transfer','Move','Openning','Sold','DeadSold','UsedSubmit'));
		$this->addField('to_branch_id')->defaultValue(0);
		$this->addField('is_used_submit')->type('boolean')->defaultValue(0);
		// $this->addField('to_row')->defaultValue(0);
		// $this->addField('from_container')->defaultValue(0);
		// $this->addField('from_row')->defaultValue(0);

		$this->addHook('beforeDelete',$this);
		$this->addHook('afterInsert',$this);
		$this->addHook('beforeSave',$this);
		
		$this->setOrder('created_at','asc');
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
			case 'Openning':
				$criq_model = $this->add('Model_Stock_ContainerRowItemQty');						
				$item = $this->add('Model_Stock_Item')->load($transaction['item_id']);
				$stock = $criq_model->getItemFromGeneral($item,null);					
				
				if($stock['qty'] >= $transaction['qty']){
					$criq_model->destroyStockFromGeneral($item,$transaction['qty']);
				}else
					throw $this->exception("cannot Delete this opening stock ( hint for delete move item into GENERAL )",'ValidityCheck')->setField('item_id');
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

	function beforeSave($transaction){
		switch ($transaction['transaction_type']) {
			case 'Openning':
				$tmp = $this->add('Model_Stock_Transaction');
				$tmp->addCondition('transaction_type','Openning');
				$tmp->addCondition('item_id',$this['item_id']);
				$tmp->addCondition('branch_id',$this['branch_id']);

				if($this->loaded()){
					$tmp->addCondition('id','<>',$this->id);
				}

				$tmp->tryLoadAny();
				if($tmp->loaded())
					throw $this->exception("Opening Stock of Item ( ".$tmp['item']." ) Already Exists with Qty ( ".$tmp['qty']." )",'ValidityCheck')->setField('item_id');	
			break;	
		}

	}

	function purchase($supplier,$item,$qty,$rate,$narration,$branch=null){
		
		if($this->loaded())
			throw $this->exception('Please Call On Empty Object','Transaction');
		if(!$supplier->loaded() and (!$supplier instanceof Model_Stock_Supplier))
			throw $this->exception('Please Pass loaded object of Supplier');
		
		if(!$item->loaded() and (!$item instanceof Model_Stock_Item))
			throw $this->exception('Please Pass loaded object of Item');

		if(!$branch)
			$branch=$this->api->currentBranch;
		
		$container = $this->add('Model_Stock_Container');
		$container->loadGeneralContainer($this->api->current_branch->id);
		$row = $this->add('Model_Stock_Row');
		$row->loadGeneralRow($this->api->current_branch->id);

		$this['member_id'] = $supplier->id;
		$this['branch_id'] = $branch->id;
		$this['item_id'] = $item->id;
		$this['qty'] = $qty;
		$this['rate'] = $rate;
		$this['amount'] = $rate;
		$this['transaction_type'] = 'Purchase';
		$this['narration'] = $narration;
		$this['to_container'] = $container->id;
		$this['to_row'] = $row->id;
		$this->save();		
	}

	function purchaseReturn($supplier,$item,$qty,$rate,$narration,$branch=null){
		if($this->loaded())
			throw $this->exception('Please Call On Empty Object');
		if(!$branch)
				$branch=$this->api->currentBranch;
		if(!($supplier instanceof Model_Stock_Supplier) and !$supplier->loaded())
			throw $this->exception('Please pass loaded object of Supplier');
		if(!($item instanceof Model_Stock_Item) and !$item->loaded())
			throw $this->exception('Please pass loaded object of Item');
		if(!$item_purchased=$this->isPurchased($supplier,$item,$branch))
			throw $this->exception("Item ( ".$item['name']." ) is Not Purchase in Given Branch with Suppllier ( ".$supplier['name']." )");
		
		$criq_model = $this->add('Model_Stock_ContainerRowItemQty');	
		$item_in_general_stock = $criq_model->getItemFromGeneral($item);
		
		if( $qty > $item_in_general_stock['qty']){
			throw $this->exception('No Enough Item in General Conatiner');
		}	

		$container = $this->add('Model_Stock_Container');
		$container->loadGeneralContainer($this->api->current_branch->id);
		$row = $this->add('Model_Stock_Row');
		$row->loadGeneralRow($this->api->current_branch->id);

		$tra=$this->add('Model_Stock_Transaction');
		$tra['member_id']=$supplier->id;
		$tra['branch_id']=$branch->id;
		$tra['item_id']=$item->id;
		$tra['qty']=$qty;
		$tra['rate']=$rate;//$item_purchased['rate'];
		$tra['amount']=$qty;
		$tra['transaction_type']='PurchaseReturn';
		$tra['narration']=$narration;
		$this['from_container'] = $container->id;
		$this['from_row'] = $row->id;
		$tra->save();

		// DO after purchase return remove item from general stock
			//removing item from general stock is done at aftersave Hook
	}

	function isPurchased($party,$item,$branch=null){
		if(!$branch)
			$branch=$this->api->currentBranch;
		$tra=$this->add('Model_Stock_Transaction');
		$tra->addCondition('member_id',$party->id);
		$tra->addCondition('branch_id',$branch->id);
		$tra->addCondition('item_id',$item->id);
		$tra->addCondition('transaction_type','Purchase');
		$tra->tryLoadAny();
		if($tra->loaded())
			return $tra;
		else
			return false;

	}

	function transfer($from_branch_id,$container,$row,$item,$qty,$narration,$to_branch,$is_used_submit=false,$as_on=null){
		

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
		
		$to_container = $this->add('Model_Stock_Container');
		$to_row = $this->add('Model_Stock_Row');
		
		if($is_used_submit){
			$to_container = $to_container->loadUsedDefaultContainer($to_branch->id);
			$to_row = $to_row->loadUsedDefaultRow($to_branch->id);
		}else {
			$to_container = $to_container->loadGeneralContainer($to_branch->id);
			$to_row = $to_row->loadGeneralRow($to_branch->id);
		}
		

		$this['item_id']=$item->id;
		$this['branch_id']=$this->api->currentBranch->id;
		$this['to_branch_id']=$to_branch->id;
		$this['qty']=$qty;
		$this['narration']=$narration;
		$this['transaction_type']='Transfer';
		$this['rate']=( $item->getAvgRate($this->api->now)  * $qty );
		$this['amount']=$this['rate'];
		$this['from_container_id'] = $container->id;
		$this['from_row_id'] = $row->id;
		$this['to_container_id'] = $to_container->id;
		$this['to_row_id'] = $to_row->id;
		$this['is_used_submit'] = $is_used_submit;
		
		$this->save();
	}

	function issue($item,$qty,$narration,$staff=null,$agent=null,$dealer=null,$from_container,$from_row,$on_date=null,$branch=null){

		if($staff->loaded() + $dealer->loaded() + $agent->loaded() > 1)
			throw $this->exception('Only One of Satff/Dealer/Agent is required', 'ValidityCheck')->setField('qty');

		if($this->loaded())
			throw $this->exception('Please call on empty Object');
		if(!($item instanceof Model_Stock_Item) and !$item->loaded() )
			throw $this->exception('Please loaded object of Item Model');
		
		if(!$on_date)
			$on_date=$this->api->today;

		if(!$branch)
			$branch = $this->api->currentBranch->id;
		// //todo item Qty Check
		// if($item->getQty($on_date) < $qty)
		// 	throw $this->exception('This Is not availeble in such Qty', 'ValidityCheck')->setField('qty');

		$this['item_id']=$item->id;
		$this['branch_id']=$branch;
		$this['qty']=$qty;
		$this['transaction_type']='Issue';
		$this['issue_date']=$this->api->now;
		if($staff->loaded())
			$this['member_id']=$staff->id;
		if($agent->loaded())
			$this['member_id']=$agent->id;
		if($dealer->loaded())
			$this['member_id']=$dealer->id;
		$this['narration']=$narration;
		$this['rate']=$item->getAvgRate($this->api->now);
		$this['amount']=$this['rate'];
		$this['from_container'] = $from_container['id'];
		$this['from_row'] = $from_row['id'];
		$this->save();

	}

	function consume($item,$qty,$narration,$staff=null,$agent=null,$dealer=null,$from_container,$from_row,$branch=null,$on_date=null){

		if($staff->loaded() + $dealer->loaded() + $agent->loaded() > 1)
			throw $this->exception('Only One of Satff/Dealer/Agent is required', 'ValidityCheck')->setField('qty');

		if($this->loaded())
			throw $this->exception('Please call on empty Object');
		if(!($item instanceof Model_Stock_Item) and !$item->loaded() )
			throw $this->exception('Please loaded object of Item Model');
		
		if(!$on_date)
			$on_date=$this->api->today;
		
		if(!$branch)
			$branch = $this->api->currentBranch->id;

		//TODO check item qty 	
		// if($item->getQty($on_date)<$qty)
		// 	throw $this->exception('This Is not availeble in such Qty', 'ValidityCheck')->setField('qty');

		$this['item_id']=$item->id;
		$this['branch_id']=$branch;
		$this['qty']=$qty;
		$this['transaction_type']='Consume';
		$this['issue_date']=$this->api->now;
		if($staff->loaded())
			$this['member_id']=$staff->id;
		if($agent->loaded())
			$this['member_id']=$agent->id;
		if($dealer->loaded())
			$this['member_id']=$dealer->id;
		$this['narration']=$narration;
		$this['rate']=$item->getAvgRate($this->api->now);
		$this['amount']=$this['rate'];
		$this['from_container'] = $from_container['id'];
		$this['from_row'] = $from_row['id'];
		$this->save();

	}

	function submit($item,$qty,$narration,$staff=null,$agent=null,$dealer=null,$branch=null,$on_date=null){
		if($staff->loaded() + $dealer->loaded() + $agent->loaded() > 1)
			throw $this->exception('Only One of Satff/Dealer/Agent is required', 'ValidityCheck')->setField('qty');

		if($this->loaded())
			throw $this->exception('Please call on empty Object');
		if($item->loaded() and (!$item instanceof Model_Stock_Item))
			throw $this->exception('Please loaded object of Item Model');
	
		if(!$on_date)
			$as_on=$this->api->today;
		if(!$branch)
			$branch = $this->api->currentBranch->id;	
		
		$container = $this->add('Model_Stock_Container');
		$container->loadGeneralContainer($this->api->current_branch->id);
		$row = $this->add('Model_Stock_Row');
		$row->loadGeneralRow($this->api->current_branch->id);

		$this['item_id']=$item->id;
		$this['branch_id']=$branch;
		$this['qty']=$qty;
		$this['transaction_type']='Submit';
		$this['narration']=$narration;
		$this['submit_date']=$this->api->now;
		$this['rate']=$item->getAvgRate($this->api->now);	
		if($staff->loaded())
			$this['member_id']=$staff->id;
		if($agent->loaded())
			$this['member_id']=$agent->id;
		if($dealer->loaded())
			$this['member_id']=$dealer->id;
		$this['amount'] = $this['rate'];
		$this['to_container'] = $container['id'];
		$this['to_row'] = $row['id'];
		$this->save();
	}	

	function dead($item,$qty,$narration,$staff=null,$agent=null,$dealer=null,$as_on=null,$branch=null){

		if($staff->loaded() + $dealer->loaded() + $agent->loaded() > 1)
			throw $this->exception('Only One of Satff/Dealer/Agent is required', 'ValidityCheck')->setField('qty');
		if($this->loaded())
			throw $this->exception('Please call on empty Object');
		if(!$item->loaded() and (!$item instanceof Model_Stock_Item))
			throw $this->exception('Please loaded object of Item Model');
		if(!$branch)
			$branch=$this->api->currentBranch;

		$container = $this->add('Model_Stock_Container');
		$container->loadDeadContainer($this->api->current_branch->id);
		$row = $this->add('Model_Stock_Row');
		$row->loadDeadRow($this->api->current_branch->id);

		$this['item_id']=$item->id;
		$this['branch_id']=$branch->id;
		$this['qty']=$qty;
		$this['narration']=$narration;
		$this['transaction_type']='DeadSubmit';
		if($staff->loaded())
			$this['member_id']=$staff->id;
		if($agent->loaded())
			$this['member_id']=$agent->id;
		if($dealer->loaded())
			$this['member_id']=$dealer->id;
		$this['rate']=$item->getAvgRate($this->api->now);
		$this['amount'] = $this['rate'];
		$this['to_container'] = $container['id'];
		$this['to_row'] = $row['id'];
		$this->save();
	}

	function openning($item,$qty,$rate,$narration,$branch=null){
		if($this->loaded())
			throw $this->exception('Please Call On Empty Object','Transaction');
		
		if(!$item->loaded() and (!$item instanceof Model_Stock_Item))
			throw $this->exception('Please Pass loaded object of Item');

		if(!$branch)
			$branch=$this->api->currentBranch;
		$container = $this->add('Model_Stock_Container');
		$container->loadGeneralContainer($this->api->current_branch->id);
		$row = $this->add('Model_Stock_Row');
		$row->loadGeneralRow($this->api->current_branch->id);

		$this['branch_id'] = $branch->id;
		$this['item_id'] = $item->id;
		$this['qty'] = $qty;
		$this['rate'] = $rate;
		$this['amount'] = $rate;
		$this['transaction_type'] = 'Openning';
		$this['narration'] = $narration;
		$this['to_container'] = $container['id']; 
		$this['to_row'] = $row['id'];
		$this->save();

	}

	function deadSold($item,$qty,$rate,$narration,$branch=null){

		if($this->loaded())
			throw $this->exception('Please call on empty Object');
		if(!($item instanceof Model_Stock_Item) and $item->loaded())
			throw $this->exception('Please loaded object of Item Model');
		
		if(!$branch)
			$branch=$this->api->currentBranch;

		$container = $this->add('Model_Stock_Container');
		$container->loadDeadContainer($this->api->current_branch->id);
		$row = $this->add('Model_Stock_Row');
		$row->loadDeadRow($this->api->current_branch->id);

		$this['item_id']=$item->id;
		$this['branch_id']=$branch->id;
		$this['qty']=$qty;
		$this['rate']=$rate;
		$this['narration']=$narration;
		$this['transaction_type']='DeadSold';
		$this['amount']=$qty*$rate;
		$this['from_container'] = $container['id'];		
		$this['from_row'] = $row['id'];		
		$this->save();
	}

	function remove(){
		
	}
	
	function move($from_container,$from_row,$item,$qty,$narration,$to_container,$to_row,$as_on=null,$branch=null){

		if($this->loaded())
			throw $this->exception('Please call on empty Object');
		if($from_container->loaded() and (!$from_container instanceof Model_Stock_Container))
			throw $this->exception('loaded object of from Container Model');			
		if($from_row->loaded() and (!$from_row instanceof Model_Stock_row))
			throw $this->exception('loaded object of from Row Model');
		if($item->loaded() and (!$item instanceof Model_Stock_Item))
			throw $this->exception('loaded object of Item Model');
		if($to_container->loaded() and (!$to_container instanceof Model_Stock_Container))
			throw $this->exception('loaded object of from Container Model');			
		if($to_row->loaded() and (!$to_row instanceof Model_Stock_row))
			throw $this->exception('loaded object of from Row Model');
							
		if(!$as_on)
			$as_on=$this->api->today;
		if(!$branch)
			$branch = $this->api->currentBranch->id;
		
		$criq_model = $this->add('Model_Stock_ContainerRowItemQty');
		if($criq_model->getItemQty($from_container,$from_row,$item) < $qty)
			throw $this->exception('This Is not availeble in such Qty', 'ValidityCheck')->setField('qty');

		$this['item_id']=$item->id;
		$this['branch_id']=$branch;
		$this['to_branch_id']=$branch;
		$this['qty']=$qty;
		$this['narration']=$narration;
		$this['transaction_type']='Move';
		$this['rate']=$item->getAvgRate($this->api->now);
		$this['amount']=$this['rate'];
		$this['from_container_id'] = $from_container->id;
		$this['from_row_id'] = $from_row->id;
		$this['to_container_id'] = $to_container->id;
		$this['to_row_id'] = $to_row->id;
		$this->save();

		$criq_model = $this->add('Model_Stock_ContainerRowItemQty');
		$criq_model->removeStock($from_container,$from_row,$item,$qty);
			//add general stock in to_branch (transferd)
		$criq1_model = $this->add('Model_Stock_ContainerRowItemQty');	
		$criq1_model->addStock($to_container,$to_row,$item,$qty);	

	}

	function getTransactionOpeningQty($item,$transaction_type,$from_date,$to_date){		
		// if($this->loaded())
		// 	throw new Exception("model be loaded".$this->api->currentBranch->id);
		$tra_model = $this->add('Model_Stock_Transaction');		
		$tra_model->addCondition('branch_id',$this->api->currentBranch->id);
		$tra_model->addCondition('item_id',$item);
		$tra_model->addCondition('transaction_type','Openning');
		$tra_model->tryLoadAny();
		$opening_qty = $tra_model->sum('qty')->getOne();

		$tra2_model = $this->add('Model_Stock_Transaction');
		$tra2_model->addCondition('branch_id',$this->api->currentBranch->id);
		$tra2_model->addCondition('item_id',$item);
		if($transaction_type){			
			$tra2_model->addCondition('transaction_type',$transaction_type);
		}
		if(!$from_date){	
			$from_date = $this->api->now;
		}
		$tra2_model->addCondition('created_at','<',$from_date);		
		$tra2_model->tryLoadAny();
		$tra_qty = $tra2_model->sum('qty')->getOne();	
		
		return ($opening_qty + $tra_qty); 	
	}	


	function usedSubmit($item,$qty,$narration,$staff=null,$agent=null,$dealer=null,$as_on=null,$branch=null){

		if($staff->loaded() + $dealer->loaded() + $agent->loaded() > 1)
			throw $this->exception('Only One of Satff/Dealer/Agent is required', 'ValidityCheck')->setField('qty');
		if($this->loaded())
			throw $this->exception('Please call on empty Object');
		if(!$item->loaded() and (!$item instanceof Model_Stock_Item))
			throw $this->exception('Please loaded object of Item Model');
		if(!$branch)
			$branch=$this->api->currentBranch;

		$container = $this->add('Model_Stock_Container');
		$container->loadUsedDefaultContainer($this->api->current_branch->id);
		$row = $this->add('Model_Stock_Row');
		$row->loadUsedDefaultRow($this->api->current_branch->id);

		$this['item_id']=$item->id;
		$this['branch_id']=$branch->id;
		$this['qty']=$qty;
		$this['narration']=$narration;
		$this['transaction_type']='UsedSubmit';
		//$this['is_used_submit']=1;
		if($staff->loaded())
			$this['member_id']=$staff->id;
		if($agent->loaded())
			$this['member_id']=$agent->id;
		if($dealer->loaded())
			$this['member_id']=$dealer->id;
		$this['rate']=$item->getAvgRate($this->api->now);
		$this['amount'] = $this['rate'];
		$this['to_container'] = $container['id'];
		$this['to_row'] = $row['id'];
		$this->save();
	}
}