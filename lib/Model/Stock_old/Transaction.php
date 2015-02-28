<?php

class Model_Stock_Transaction extends Model_Table {
	var $table= "stock_transactions";
	function init(){  
		parent::init();
		$this->hasOne('Stock_Item','item_id');
		$this->hasOne('Stock_Party','party_id');
		$this->hasOne('Staff','staff_id');
		$this->hasOne('Dealer','dealer_id');
		$this->hasOne('Agent','agent_id');
		$this->hasOne('Branch','branch_id');
		$this->hasOne('Branch','to_branch_id');
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

	function purchase($party,$item,$qty,$rate,$narration,$branch=null){

		if($this->loaded())
			throw $this->exception('Please Call On Empty Object');
		if(!$party->loaded() and (!$party instanceof Model_Stock_Party))
			throw $this->exception('Please Pass loaded object of Party');
		
		if(!$item->loaded() and (!$item instanceof Model_Stock_Item))
			throw $this->exception('Please Pass loaded object of Branch');

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
			throw $this->exception("This Item is Not Purchase in Given Branch, Check Again");
		$tra=$this->add('Model_Stock_Transaction');
		$tra['party_id']=$party->id;
		$tra['branch_id']=$branch->id;
		$tra['item_id']=$item->id;
		$tra['qty']=$qty;
		$tra['rate']=$rate;//$item_purchased['rate'];
		$tra['transaction_type']='PurchaseReturn';
		$tra['narration']=$narration;
		$tra->save();
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

	function issue($item,$qty,$narration,$staff=null,$agent=null,$dealer=null,$branch=null,$on_date=null){

		if($staff->loaded() + $dealer->loaded() + $agent->loaded() > 1)
			throw $this->exception('Only One of Satff/Dealer/Agent is required', 'ValidityCheck')->setField('qty');

		if($this->loaded())
			throw $this->exception('Please call on empty Object');
		if(!($item instanceof Model_Stock_Item) and !$item->loaded() )
			throw $this->exception('Please loaded object of Item Model');
		
		if(!$on_date)
			$on_date=$this->api->today;
		if($item->getQty($on_date)<$qty)
			throw $this->exception('This Is not availeble in such Qty', 'ValidityCheck')->setField('qty');
		$this['item_id']=$item->id;
		$this['branch_id']=$this->api->currentBranch->id;
		$this['qty']=$qty;
		$this['transaction_type']='Issue';
		$this['issue_date']=$this->api->now;
		if($staff->loaded())
			$this['staff_id']=$staff->id;
		if($agent->loaded())
			$this['agent_id']=$agent->id;
		if($dealer->loaded())
			$this['dealer_id']=$dealer->id;
		$this['narration']=$narration;
		$this->save();

	}

	function consume($item,$qty,$narration,$staff=null,$agent=null,$dealer=null,$branch=null,$on_date=null){

		if($staff->loaded() + $dealer->loaded() + $agent->loaded() > 1)
			throw $this->exception('Only One of Satff/Dealer/Agent is required', 'ValidityCheck')->setField('qty');

		if($this->loaded())
			throw $this->exception('Please call on empty Object');
		if(!($item instanceof Model_Stock_Item) and !$item->loaded() )
			throw $this->exception('Please loaded object of Item Model');
		
		if(!$on_date)
			$on_date=$this->api->today;
		if($item->getQty($on_date)<$qty)
			throw $this->exception('This Is not availeble in such Qty', 'ValidityCheck')->setField('qty');
		$this['item_id']=$item->id;
		$this['branch_id']=$this->api->currentBranch->id;
		$this['qty']=$qty;
		$this['transaction_type']='Consume';
		$this['issue_date']=$this->api->now;
		if($staff->loaded())
			$this['staff_id']=$staff->id;
		if($agent->loaded())
			$this['agent_id']=$agent->id;
		if($dealer->loaded())
			$this['dealer_id']=$dealer->id;
		$this['narration']=$narration;
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
		// if($item->getQty($as_on)<$qty)
		// 	throw $this->exception('This Is not availeble in such Qty', 'ValidityCheck')->setField('qty');
		$this['item_id']=$item->id;
		$this['branch_id']=$this->api->currentBranch->id;
		$this['qty']=$qty;
		$this['transaction_type']='Submit';
		$this['narration']=$narration;
		$this['submit_date']=$this->api->now;
		if($staff->loaded())
			$this['staff_id']=$staff->id;
		if($agent->loaded())
			$this['agent_id']=$agent->id;
		if($dealer->loaded())
			$this['dealer_id']=$dealer->id;
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
		if($item->getQty($as_on)<$qty)
			throw $this->exception('This Is not availeble in such Qty', 'ValidityCheck')->setField('qty');
		
		$this->submit($item,$qty,$narration,$staff,$agent,$dealer);
		
		$this['item_id']=$item->id;
		$this['branch_id']=$branch->id;
		$this['qty']=$qty;
		$this['narration']=$narration;
		$this['transaction_type']='Dead';

		if($staff->loaded())
			$this['staff_id']=$staff->id;
		if($agent->loaded())
			$this['agent_id']=$agent->id;
		if($dealer->loaded())
			$this['dealer_id']=$dealer->id;
		$this->save();
	}

	function transfer($item,$to_branch,$qty,$rate,$narration,$as_on=null){

		if($this->loaded())
			throw $this->exception('Please call on empty Object');
		if($item->loaded() and (!$item instanceof Model_Stock_Item))
			throw $this->exception('Please loaded object of Item Model');
		if(!$to_branch->loaded())
			throw $this->exception('Please pass loaded object of To Branch Model');
		if(!$as_on)
			$as_on=$this->api->today;
		if($item->getQty($as_on)<$qty)
			throw $this->exception('This Is not availeble in such Qty', 'ValidityCheck')->setField('qty');
		$this['item_id']=$item->id;
		$this['branch_id']=$this->api->currentBranch->id;
		$this['to_branch_id']=$to_branch->id;
		$this['qty']=$qty;
		$this['narration']=$narration;
		$this['transaction_type']='Transfer';
		$this['rate']=$rate;
		$this->save();
	}

	function openning($item,$qty,$rate,$narration,$branch=null){

		if($this->loaded())
			throw $this->exception('Please call on empty Object');
		if(!($item instanceof Model_Stock_Item) and $item->loaded())
			throw $this->exception('Please loaded object of Item Model');
		
		if(!$branch)
			$branch=$this->api->currentBranch;
		$this['item_id']=$item->id;
		$this['branch_id']=$this->api->currentBranch->id;
		$this['branch_id']=$branch->id;
		$this['qty']=$qty;
		$this['rate']=$rate;
		$this['narration']=$narration;
		$this['transaction_type']='Openning';
		$this->save();

	}

	function sold($item,$qty,$rate,$narration,$branch=null){

		if($this->loaded())
			throw $this->exception('Please call on empty Object');
		if(!($item instanceof Model_Stock_Item) and $item->loaded())
			throw $this->exception('Please loaded object of Item Model');
		
		if(!$branch)
			$branch=$this->api->currentBranch;
		$this['item_id']=$item->id;
		$this['branch_id']=$this->api->currentBranch->id;
		$this['branch_id']=$branch->id;
		$this['qty']=$qty;
		$this['rate']=$rate;
		$this['narration']=$narration;
		$this['transaction_type']='Sold';
		$this['amount']=$qty*$rate;
		$this->save();
		

	}

	function remove(){
		if($this->loaded())
			throw $this->exception('Unable To determine, Which Record To be Delete');
		$this->delete();
	}


}