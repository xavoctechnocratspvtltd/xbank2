<?php

class Model_StockNew_Item extends Model_Table {

	public $table 		= "stocknew_items";
	public $title_field = 'name_with_code';

	function init(){
		parent::init();
 
		$this->hasOne('StockNew_Category','category_id')->sortable(true);
		
		$this->addField('name')->sortable(true);
		$this->addField('code')->sortable(true);

		$this->addExpression('name_with_code')->set('CONCAT(name," [",code,"]")');
		$this->addExpression('allowed_in_transactions')->set($this->refSQL('category_id')->fieldQuery('allowed_in_transactions'));

		$this->addField('description')->type('text');
		$this->addField('is_active')->type('boolean')->defaultValue(true)->sortable(true);
		$this->addField('is_fixed_asset')->type('boolean')->defaultValue(false)->sortable(true);
		
		$this->hasMany('StockNew_Transaction','item_id');
		$this->hasMany('StockNew_ContainerRowItemQty','item_id');
 		$this->addHook('beforeDelete',$this);
 		$this->addHook('beforeSave',$this);
		$this->_dsql()->order('name','asc');

		$this->add('dynamic_model/Controller_AutoCreator');
	}
	

	function beforeSave(){
		$old_model = $this->add('Model_StockNew_Item');
		$old_model->addCondition('code',trim($this['code']));
		$old_model->addCondition('id','<>',$this->id);
		$old_model->tryLoadAny();
		if($old_model->loaded()){
			throw $this->Exception('Code is already added','ValidityCheck')->setField('code');
		}

	}

	function beforeDelete($model){
		if($this->ref('StockNew_Transaction')->count()->getOne() > 0)
			throw $this->exception('Item ( '.$model['name'].' ) Cannot Delete');
	}

	function getAvgRate($as_on){
		if(!$this->loaded())
			throw new \Exception("pass loaded model of item");
		if($as_on)
			$as_on=$this->api->now;

		//purchase and Opening
		$purchase_tra = $this->add('Model_StockNew_Transaction');
		$purchase_tra->addCondition('item_id',$this->id);
		$purchase_tra->addCondition('created_at','<',$this->api->nextDate($as_on));
		$purchase_tra->addCondition('transaction_template_type',array('Purchase','Opening'));
		$purchase_tra_amount = ($purchase_tra->sum('rate')->getOne())?:0;
		$no_of_puchase=$purchase_tra->sum('qty')->getOne()?:0;
			
		
		// $no_of_puchase=$purchase_tra->count()->getOne()?:0;
		//Purchase Return
		$return_tra = $this->add('Model_StockNew_Transaction');
		$return_tra->addCondition('item_id',$this->id);
		$return_tra->addCondition('created_at','<',$this->api->nextDate($as_on));
		$return_tra->addCondition('transaction_template_type','Purchase_Return');
		$return_tra_amount = $return_tra->sum('rate')->getOne()?:0;
		$no_of_return = $return_tra->sum('qty')->getOne()?:0;
		// $no_of_return = $return_tra->count()->getOne()?:0;

		//Transfer In
		// $transfer_in = $this->add('Model_Stock_Transaction');
		// $transfer_in->addCondition('item_id',$this->id);
		// $transfer_in->addCondition('created_at','<',$this->api->nextDate($as_on));
		// $transfer_in->addCondition('transaction_type','Transfer');
		// $transfer_in->addCondition('to_branch_id',$this->api->current_branch->id);
		// $transfer_in->addCondition('branch_id','<>',$this->api->current_branch->id);
		// $transfer_in_amount = $transfer_in->sum('rate')->getOne()?:0;
		// $no_of_transfer_in = $transfer_in->sum('qty')->getOne()?:0;
		
		//Transfer Out
		// $transfer_out = $this->add('Model_Stock_Transaction');
		// $transfer_out->addCondition('item_id',$this->id);
		// $transfer_out->addCondition('created_at','<',$this->api->nextDate($as_on));
		// $transfer_out->addCondition('transaction_type','Transfer');
		// $transfer_out->addCondition('branch_id',$this->api->current_branch->id);
		// $transfer_out->addCondition('to_branch_id','<>',$this->api->current_branch->id);
		// $transfer_out->tryLoadAny();
		// $transfer_out_amount = $transfer_out->sum('rate')->getOne()?:0;
		// $no_of_transfer_out = $transfer_out->sum('qty')->getOne()?:0;
		// throw new Exception($transfer_out_amount);

		//total amount
		$amount = $purchase_tra_amount - $return_tra_amount;
		//total qty 
		$qty =$no_of_puchase - $no_of_return;
		if(!$qty)
			$qty=1; 
		
		return $amount/$qty;
	}

}