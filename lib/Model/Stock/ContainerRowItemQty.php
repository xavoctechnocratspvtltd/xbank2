<?php

class Model_Stock_ContainerRowItemQty extends Model_Table {
	var $table= "stock_containerrowitemqty";
	
	function init(){
		parent::init();

		$this->hasOne('Branch','branch_id')->sortable(true);
		$this->hasOne('Stock_Container','container_id')->defaultValue('Null')->sortable(true);
		$this->hasOne('Stock_Row','row_id')->defaultValue('Null')->sortable(true);
		$this->hasOne('Stock_Item','item_id')->defaultValue('Null')->sortable(true);

		$this->addField('qty'); 
		
		$this->add('dynamic_model/Controller_AutoCreator');
	}

	function createNew($qty,$other_fields=array(),$form=null){

		if($this->loaded())
			throw $this->exception('Please call on unloaded Object');
		$this['qty']=$qty;
		$this['row_id']=$other_fields['row_id'];
		$this['item_id']=$other_fields['item_id'];
		$this->save();
	}

	function addStockInGeneral($item,$qty,$branch_id=null){
		if($this->loaded())
			throw $this->exception('Please Call on unloaded Object');

		if(!$branch_id)
			$branch_id = $this->api->current_branch->id;

		$container_model = $this->add('Model_Stock_Container');
		$container_model->loadGeneralContainer($branch_id);
		
		$row_model = $this->add('Model_Stock_Row');
		$row_model->loadGeneralRow($branch_id);
			if($row_model->loaded()){
				$this->addCondition('container_id',$container_model->id);
				$this->addCondition('row_id',$row_model->id);
				$this->addCondition('item_id',$item->id);
				$this->tryLoadAny();
				if($this->loaded()){
					$this['qty']=$this['qty'] + $qty;
					$this['branch_id'] = $branch_id;
					$this['container_id'] = $container_model->id;
					$this->update();	
				}else{
					$this['item_id']=$item->id;
					$this['qty']=$qty;
					$this['container_id'] = $container_model->id;
					$this['branch_id'] = $branch_id;
					$this->save();
				}
			}
	}

	function addStockInDead($item,$qty,$branch_id=null){
		if($this->loaded())
			throw $this->exception('Please Call on unloaded Object');

		if(!$branch_id)
			$branch_id = $this->api->current_branch->id;

		$container_model = $this->add('Model_Stock_Container');
		$container_model->loadDeadContainer($branch_id);
		$row_model = $this->add('Model_Stock_Row');
		$row_model->loadDeadRow();
			if($row_model->loaded()){
				$this->addCondition('container_id',$container_model->id);
				$this->addCondition('row_id',$row_model->id);
				$this->addCondition('item_id',$item->id);
				$this->tryLoadAny();
				if($this->loaded()){
					$this['qty']=$this['qty'] + $qty;
					$this['branch_id'] = $branch_id;
					$this['container_id'] = $container_model->id;
					$this->update();	
				}else{
					$this['item_id']=$item->id;
					$this['qty']=$qty;
					$this['container_id'] = $container_model->id;
					$this['branch_id'] = $branch_id;
					$this->save();
				}
			}
	}

	function addStockInUsedDefault($item,$qty,$branch_id=null){
		if($this->loaded())
			throw $this->exception('Please Call on unloaded Object');

		if(!$branch_id)
			$branch_id = $this->api->current_branch->id;

		$container_model = $this->add('Model_Stock_Container');
		$container_model->loadUsedDefaultContainer($branch_id);
		
		$row_model = $this->add('Model_Stock_Row');
		$row_model->loadUsedDefaultRow($branch_id);
			if($row_model->loaded()){
				$this->addCondition('container_id',$container_model->id);
				$this->addCondition('row_id',$row_model->id);
				$this->addCondition('item_id',$item->id);
				$this->tryLoadAny();
				if($this->loaded()){
					$this['qty']=$this['qty'] + $qty;
					$this['branch_id'] = $branch_id;
					$this['container_id'] = $container_model->id;
					$this->update();	
				}else{
					$this['item_id']=$item->id;
					$this['qty']=$qty;
					$this['container_id'] = $container_model->id;
					$this['branch_id'] = $branch_id;
					$this->save();
				}
			}
	}



	function destroyStockFromGeneral($item,$qty){
		if($this->loaded())
			throw $this->exception('Please Call on unloaded Object');

		$container_model = $this->add('Model_Stock_Container');
		$container_model->loadGeneralContainer($this->api->current_branch->id);
		$row_model = $this->add('Model_Stock_Row');
		$row_model->loadGeneralRow($this->api->current_branch->id);
			if($row_model->loaded()){
				$this->addCondition('container_id',$container_model->id);
				$this->addCondition('row_id',$row_model->id);
				$this->addCondition('item_id',$item->id);
				$this->tryLoadAny();
				if($this->loaded()){
					if($qty > $this['qty']){
						throw $this->exception("No Enough Item (".$item['name'].") in General Conatiner");					
					}
					$this['qty']=$this['qty'] - $qty;
					$this->update();	
				}
			}	
	}
	
	function destroyStockFromDead($item,$qty){
		if($this->loaded())
			throw $this->exception('Please Call on unloaded Object');

		$container_model = $this->add('Model_Stock_Container');
		$container_model->loadDeadContainer();
		$row_model = $this->add('Model_Stock_Row');
		$row_model->loadDeadRow();
			if($row_model->loaded()){
				$this->addCondition('container_id',$container_model->id);
				$this->addCondition('row_id',$row_model->id);
				$this->addCondition('item_id',$item->id);
				$this->tryLoadAny();
				if($this->loaded()){
					if($qty > $this['qty']){
						throw $this->exception("No Enough Item (".$item['name'].") in General Conatiner");					
					}
					$this['qty']=$this['qty'] - $qty;
					$this->update();	
				}
			}	
	}

	function getItemFromGeneral($item,$branch=null){
		if(!$branch)
			$branch=$this->api->currentBranch;
		
		$this->addCondition('branch_id',$branch->id);
		$this->addCondition('item_id',$item->id);
		$this->addCondition('container','General');
		$this->addCondition('row','General');
		$this->tryLoadAny();
		if($this->loaded())
			return $this;
		else
			return false;
	}
	
	function getItemQty($container,$row,$item,$branch_id=null){
		if(!$branch_id)
			$branch_id = $this->api->current_branch->id;

		$this->addCondition('branch_id',$branch_id);
		$this->addCondition('container_id',$container->id);
		$this->addCondition('row_id',$row->id);
		$this->addCondition('item_id',$item->id);
		$this->tryLoadAny();

		if($this->loaded()){
		 	return $this['qty'];
		}else
		 return -1;
	}

	function removeStock($container,$row,$item,$qty,$branch_id=null){

		if(!$branch_id)
			$branch_id = $this->api->current_branch->id;

		$old_item_qty = $this->getItemQty($container,$row,$item,$branch_id);

		if($old_item_qty >= $qty){
			$this['qty'] = $old_item_qty - $qty;
			$this->update();
		}else
			throw $this->exception("No Enough Item (".$item['name'].") in Conatiner ( ". $container['name']." )");					

	}

	function addStock($container,$row,$item,$qty,$branch_id=null){
		if($this->loaded())
			throw $this->exception('Please Call on unloaded Object');
		
		if($container->loaded() and (!$container instanceof Model_Stock_Container))
			throw $this->exception('loaded object of Container Model');			
		if($row->loaded() and (!$row instanceof Model_Stock_row))
			throw $this->exception('loaded object of Row Model');
		if($item->loaded() and (!$item instanceof Model_Stock_Item))
			throw $this->exception('loaded object of Item Model');

		if(!$branch_id)
			$branch_id = $this->api->current_branch->id;


			if($row->loaded()){
				$this->addCondition('container_id',$container->id);
				$this->addCondition('row_id',$row->id);
				$this->addCondition('item_id',$item->id);
				$this->tryLoadAny();
				if($this->loaded()){
					$this['qty']=$this['qty'] + $qty;
					$this['branch_id'] = $branch_id;
					$this['container_id'] = $container->id;
					$this->update();	
				}else{
					$this['item_id']=$item->id;
					$this['qty']=$qty;
					$this['container_id'] = $container->id;
					$this['branch_id'] = $branch_id;
					$this->save();
				}
			}
	}	

}