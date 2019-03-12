<?php


class Form_StockTransaction extends Form_Stacked {
	
	public $transaction_template_model=null;

	function init(){
		parent::init();

		if(!$this->transaction_template_model)
			throw new Exception("Transaction Template Name not defined", 1);
		
		$model = $this->transaction_template_model;
		$this->add('H1')->set($model['name']);

		$from_filter_chain_fields = [];
		if($model['from_branch']){
			$this->addField('DropDown','from_branch_id')->setEmptyText("Please select")->validateNotNull()->setModel('ActiveBranch');
			$from_filter_chain_fields['branch_field'] = 'from_branch_id';
		}
		
		if($model['always_from_current_branch'])
			$this->add('View')->set('always_from_current_branch');

		if($model['from_supplier'])
			$this->addField('autocomplete/Basic','from_supplier_id')->setModel('StockNew_Supplier');

		if($model['from_staff'])
			$this->addField('autocomplete/Basic','from_staff_id')->setModel('StockNew_Staff');

		if($model['from_agent'])
			$this->addField('autocomplete/Basic','from_agent_id')->setModel('StockNew_Agent');

		if($model['from_dealer'])
			$this->addField('autocomplete/Basic','from_dealer_id')->setModel('StockNew_Dealer');

		if($model['from_container']){
			$t=$this->add('Model_StockNew_Container');
			if(trim($model['from_container_types'])){
				$t->addCondition('containertype',array_map('trim',explode(",", $model['from_container_types'])));
			}
			
			if($model['always_from_current_branch'])
				$t->addCondition('branch_id',$this->app->current_branch->id);

			$this->addField('DropDown','from_container_id')
				->setEmptyText("Please select")
				->validateNotNull()
				->setModel($t);

			$from_filter_chain_fields['container_field'] = 'from_container_id';
		}

		if($model['from_container_row']){
			$t=$this->add('Model_StockNew_ContainerRow');
			$this->addField('DropDown','from_container_row_id')
				->setEmptyText("Please select")
				->validateNotNull()
				->setModel($t)
				;

			$from_filter_chain_fields['container_row_field'] = 'from_container_row_id';
		}

		if($model['from_default_container_row'])
			$this->add('View')->set('from_default_container_row');

		if($model['check_from_qty'])
			$this->add('View')->set('Qty will be checked for availability');

		if($model['items']){
			$m = $this->add('Model_StockNew_Item');
			$m->addCondition([['allowed_in_transactions','is',null],['allowed_in_transactions','[]'],['allowed_in_transactions','like','%"'.$model['name'].'"%']]);
			$this->addField('autocomplete/Basic','item_id')->validateNotNull()->setModel($m);
		}

		if($model['qty'])
			$this->addField('qty')->validateNotNull();

		// From Branch container row chain controller
		$this->add('Controller_StockNewFieldFilter',$from_filter_chain_fields);

		$this->add('HR');

		$to_filter_chain_fields=[];

		if($model['to_branch']){
			$this->addField('DropDown','to_branch_id')->setEmptyText("Please select")->validateNotNull()->setModel('ActiveBranch');
			$to_filter_chain_fields['branch_field'] = 'to_branch_id';
		}

		if($model['always_to_current_branch'])
			$this->add('View')->set('always_to_current_branch');

		if($model['to_supplier'])
			$this->addField('autocomplete/Basic','to_supplier_id')->setModel('StockNew_Supplier');

		if($model['to_staff'])
			$this->addField('autocomplete/Basic','to_staff_id')->setModel('StockNew_Staff');

		if($model['to_agent'])
			$this->addField('autocomplete/Basic','to_agent_id')->setModel('StockNew_Agent');

		if($model['to_dealer'])
			$this->addField('autocomplete/Basic','to_dealer_id')->setModel('StockNew_Dealer');

		if($model['to_container']){
			$t=$this->add('Model_StockNew_Container');
			if(trim($model['to_container_types'])){
				$t->addCondition('containertype',array_map('trim',explode(",", $model['to_container_types'])));
			}

			if($model['always_to_current_branch'])
				$t->addCondition('branch_id',$this->app->current_branch->id);

			$this->addField('DropDown','to_container_id')
				->setEmptyText("Please select")
				->validateNotNull()
				->setModel($t);

			$to_filter_chain_fields['container_field'] = 'to_container_id';
		}

		if($model['to_container_row']){
			$t=$this->add('Model_StockNew_ContainerRow');
			$this->addField('DropDown','to_container_row_id')
				->setEmptyText("Please select")
				->validateNotNull()
				->setModel($t);
			$to_filter_chain_fields['container_row_field'] = 'to_container_row_id';
		}

		$this->add('Controller_StockNewFieldFilter',$to_filter_chain_fields);

		if($model['to_default_container_row'])
			$this->add('View')->set('to_default_container_row');

		if($model['rate'])
			$this->addField('Money','rate')->validateNotNull();

		if($model['narration'])
			$this->addField('Text','narration');

		$this->addSubmit('Execute');

		$this->addHook('validate',$this);
	}

	function validate(){
		$model = $this->transaction_template_model;
		$trm = $this->add('Model_StockNew_Transaction');
		
		$from_branch_id = $model['always_from_current_branch']?$this->app->current_branch->id:$this['from_branch_id'];

		// check if row belongs to container selected if any
		// check if container belongs to branch selected if any or current branch id option setted so

		// if qty to check
		// check qty in container (Selected or default if check_qty_from is set)

		

		if($model['check_from_qty']){
			if($this['from_staff_id'] || $this['from_agent_id'] || $this['from_dealer_id'] ){
				$from_member_id=$this['from_supplier_id']?:$this['from_staff_id']?:$this['from_agent_id']?:$this['from_dealer_id'];
				$item_stock = $this->add('Model_StockNew_ItemStock',['from_member_id'=>$from_member_id]);
				$item_stock->load($this['item_id']);

			}else{			
				$row_to_check = $this->add('Model_StockNew_ContainerRow');
				
				if($model['from_default_container_row']){				
					$row_to_check->loadDefault($from_branch_id);
				}elseif($this['from_container_row_id']){
					$row_to_check->load($this['from_container_row_id']);
				}else{
					throw new \Exception("TransactionTemplate says to check qty, but not defined from where?", 1);
				}

				$item_stock = $this->add('Model_StockNew_ItemStock',['for_container_row_id'=>$row_to_check->id]);
				$item_stock->load($this['item_id']);

			}

			if($item_stock['net_stock'] < $this['qty'])
				throw new \Exception("Stock Unavailable to process, Available stock : ". $item_stock['net_stock']." Requested Stock ".$this['qty'], 1);
		}

		$selected_item = $this->add('Model_StockNew_Item')->load($this['item_id']);
		
		if( $selected_item['is_fixed_asset'] && $this['qty'] != 1 ){
			throw new \Exception("Fixed Assests must have exactly '1' in qty", 1);
		}								
						
	}

	function process(){
		$model = $this->transaction_template_model;

		$trm = $this->add('Model_StockNew_Transaction');

		$from_branch_id = $model['always_from_current_branch']?$this->app->current_branch->id:$this['from_branch_id'];
		$to_branch_id = $model['always_to_current_branch']?$this->app->current_branch->id:$this['to_branch_id'];

		$default_from_row=null;
		$default_from_row_container=null;
		if($model['from_default_container_row']){
			$row_to_check = $this->add('Model_StockNew_ContainerRow');
			$row_to_check->loadDefault($from_branch_id);
			$default_from_row = $row_to_check->id;
			$default_from_row_container = $row_to_check['container_id'];
		}

		$default_to_row=null;
		$default_to_row_container=null;
		if($model['to_default_container_row']){
			$row_to_check = $this->add('Model_StockNew_ContainerRow');
			$row_to_check->loadDefault($to_branch_id);
			$default_to_row = $row_to_check->id;
			$default_to_row_container = $row_to_check['container_id'];
		}

		$trm['transaction_template_type_id']= $model->id;
		$trm['from_branch_id'] 				= $from_branch_id;
		$trm['from_member_id'] 				= $this['from_supplier_id']?:$this['from_staff_id']?:$this['from_agent_id']?:$this['from_dealer_id'];
		$trm['from_container_id'] 			= $default_from_row_container?:$this['from_container_id'];
		$trm['from_container_row_id'] 		= $default_from_row?:$this['from_container_row_id'];
		$trm['to_branch_id'] 				= $to_branch_id;
		$trm['to_member_id'] 				= $this['to_supplier_id']?:$this['to_staff_id']?:$this['to_agent_id']?:$this['to_dealer_id'];
		$trm['to_container_id'] 			= $default_to_row_container?:$this['to_container_id'];
		$trm['to_container_row_id'] 		= $default_to_row?:$this['to_container_row_id'];
		$trm['item_id'] 					= $this['item_id'];
		$trm['qty'] 						= $this['qty'];
		$trm['rate'] 						= $this['rate'];
		$trm['narration'] 					= $this['narration'];

		$trm->save();

		
		
	}
}