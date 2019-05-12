<?php

class Model_StockNew_ItemStock extends Model_StockNew_Item {
	
	public $for_branch_id			= null;
	public $for_container_id 		= null;
	public $for_container_row_id	= null;
	public $for_member_id			= null;
	public $for_category_id			= null;
	public $for_item_id				= null;

	function init(){
		parent::init();

		$transaction_templates = $this->add('Model_StockNew_TransactionTemplate');

		$add_expressions = [];
		$sub_expressions = [];

		foreach ($transaction_templates as $trt) {
			$trt_id= $trt->id;
			if($trt['add_qty_to']){
				$add_expressions[] = $expname = 'add_'.strtolower($this->app->normalizeName($trt['name']));
				$this->addExpression($expname)->set(function($m,$q)use($expname,$trt_id){
					$tr_row = $this->add('Model_StockNew_Transaction',['table_alias'=>$expname]);
					if($this->for_branch_id) $tr_row->addCondition('to_branch_id',$this->for_branch_id);
					if($this->for_container_id) $tr_row->addCondition('to_container_id',$this->for_container_id);
					if($this->for_container_row_id) $tr_row->addCondition('to_container_row_id',$this->for_container_row_id);
					if($this->for_member_id) $tr_row->addCondition('to_member_id',$this->for_member_id);
					$tr_row->addCondition('item_id',$m->getElement('id'));
					$tr_row->addCondition('transaction_template_type_id',$trt_id);
					return $q->expr('IFNULL([0],0)',[$tr_row->sum('qty')]);
				});
			}

			if($trt['subtract_qty_from']){
				$sub_expressions[] = $expname = 'sub_'. strtolower($this->app->normalizeName($trt['name']));
				$this->addExpression($expname)->set(function($m,$q)use($expname,$trt_id){
					$tr_row = $this->add('Model_StockNew_Transaction',['table_alias'=>$expname]);
					if($this->for_branch_id) $tr_row->addCondition('from_branch_id',$this->for_branch_id);
					if($this->for_container_id) $tr_row->addCondition('from_container_id',$this->for_container_id);
					if($this->for_container_row_id) $tr_row->addCondition('from_container_row_id',$this->for_container_row_id);
					if($this->for_member_id) $tr_row->addCondition('from_member_id',$this->for_member_id);
					$tr_row->addCondition('item_id',$m->getElement('id'));
					$tr_row->addCondition('transaction_template_type_id',$trt_id);
					return $q->expr('IFNULL([0],0)',[$tr_row->sum('qty')]);
				});
			}

		}


		$this->addExpression('total_in')->set(function($m,$q)use($add_expressions){
			$expr_str_array 	= array_map(function($el){return 'IFNULL(['.$el.'],0)';},$add_expressions);
			$expr_str 			= implode("+", $expr_str_array);
			$expr_option_array	= [];

			foreach ($add_expressions as $exp) {
				$expr_option_array[$exp] = $m->getElement($exp);
			}
			return $q->expr("".$expr_str."",$expr_option_array);
		});

		$this->addExpression('total_out')->set(function($m,$q)use($sub_expressions){
			$expr_str_array = array_map(function($el){return 'IFNULL(['.$el.'],0)';},$sub_expressions);
			$expr_str = implode("+", $expr_str_array);
			$expr_option_array=[];

			foreach ($sub_expressions as $exp) {
				$expr_option_array[$exp] = $m->getElement($exp);
			}

			return $q->expr("".$expr_str."",$expr_option_array);
		});

		$this->addExpression('net_stock')->set(function($m,$q){
			return $q->expr('IFNULL([0],0) - IFNULL([1],0)',[$m->getElement('total_in'),$m->getElement('total_out')]);
		})->type('number')->sortable(true);

	}
}