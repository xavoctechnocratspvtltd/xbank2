<?php


class Form_StockTransaction extends Form {
	
	public $transaction_template_model=null;

	function init(){
		parent::init();

		if(!$this->transaction_template_model)
			throw new Exception("Transaction Template Name not defined", 1);
		
		$model = $this->transaction_template_model;
		$this->add('H1')->set($model['name']);


		if($model['from_branch'])
			$this->addField('DropDown','from_branch_id')->setModel('ActiveBranch');

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
			$this->addField('autocomplete/Basic','from_container_id')->setModel($t);
		}

		if($model['from_containerrow']){
			$t=$this->add('Model_StockNew_ContainerRow');
			$this->addField('autocomplete/Basic','from_container_row_id')->setModel($t);
		}


		if($model['to_branch'])
			$this->addField('DropDown','to_branch_id')->setModel('ActiveBranch');

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
			$this->addField('autocomplete/Basic','to_container_id')->setModel($t);
		}

		if($model['to_containerrow']){
			$t=$this->add('Model_StockNew_ContainerRow');
			$this->addField('autocomplete/Basic','to_container_row_id')->setModel($t);
		}

		if($model['items'])
			$this->addField('autocomplete/Basic','item_id')->setModel('StockNew_Item');

		if($model['qty'])
			$this->addField('qty');

		if($model['rate'])
			$this->addField('Money','rate');

		if($model['narration'])
			$this->addField('Text','narration');

		$this->addSubmit('Execute');

		$this->addHook('validate',$this);
	}

	function validate(){
		$model = $this->transaction_template_model;
		$trm = $this->add('Model_StockNew_Transaction');
		

		// check if row belongs to container selected if any
		// check if container belongs to branch selected if any

		// if qty to check
		// check qty in container (Selected or default if from default is set)

				
	}

	function process(){
		$model = $this->transaction_template_model;

		$trm = $this->add('Model_StockNew_Transaction');

		$trm['transaction_template_type_id'] = $model->id;
		$trm['from_branch_id'] = $model['always_from_current_branch']?$this->app->current_branch->id:$this['from_branch_id'];
		$trm['from_member_id'] = $this['from_supplier_id']?:$this['from_staff_id']?:$this['from_agent_id']?:$this['from_dealer_id'];
		$trm['from_container_id'] = $this['from_container_id'];
		$trm['from_container_row_id'] = $this['from_container_row_id'];
		$trm['to_branch_id'] = $model['always_to_current_branch']?$this->app->current_branch->id:$this['to_branch_id'];
		$trm['to_member_id'] = $this['to_supplier_id']?:$this['to_staff_id']?:$this['to_agent_id']?:$this['to_dealer_id'];
		$trm['to_container_id'] = $this['to_container_id'];
		$trm['to_container_row_id'] = $this['to_container_row_id'];
		$trm['item_id'] = $this['item_id'];
		$trm['qty'] = $this['qty'];
		$trm['rate'] = $this['rate'];
		$trm['narration'] = $this['narration'];

		$trm->save();

		
		
	}
}