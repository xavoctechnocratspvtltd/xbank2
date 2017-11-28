<?php

class page_stock_reports_stock extends Page {
	function init(){
		parent::init();

		$self = $this;
		$form = $this->add('Form');
		$form->addField('DatePicker','to_date');
		// $form->addField('DatePicker','from_date');
		$form->addSubmit('GET LIST');

		$item_model = $this->add('Model_Stock_Item');
		// $item_j=$item_model->join('stock_transactions.item_id','id');
		// $item_j->addField('created_at');
		// $item_j->addField('transaction_type');

		$container = $this->add('Model_Stock_Container');
		$container->addCondition('branch_id',$this->api->currentBranch->id);
		$this->used_container_id = [];
		foreach ($container as $cont) {
			if(preg_match('/used/',strtolower($cont['name'])))
				$this->used_container_id[$cont['id']] = $cont['id'];
		}
		
		$grid = $this->add('Grid_AccountsBase');
		$grid->addSno();
 		
		$grid->addMethod('format_openning',function($g,$f){
			$openning_tra = $g->add('Model_Stock_Transaction',array('table_alias'=>'xt'));
			$item_j=$openning_tra->join('stock_items','item_id');
			$openning_tra->addCondition('created_at','<',$g->api->nextDate($_GET['to_date']?:$g->api->now));
			$openning_tra->addCondition('transaction_type','Openning');
			$openning_tra->addCondition('branch_id',$g->api->currentBranch->id);
			$openning_tra->addCondition('item_id',$g->model->id);
			$openning_tra_qty = ($openning_tra->sum('qty')->getOne())?:0;

			$g->current_row_html[$f]=$openning_tra_qty;
		});

		$grid->addMethod('format_purchase',function($g,$f){
			$purchase_tra = $g->add('Model_Stock_Transaction',array('table_alias'=>'xt'));
			$item_j=$purchase_tra->join('stock_items','item_id');
			$purchase_tra->addCondition('branch_id',$g->api->currentBranch->id);
			$purchase_tra->addCondition('created_at','<',$g->api->nextDate($_GET['to_date']?:$g->api->now));
			$purchase_tra->addCondition('transaction_type','Purchase');
			$purchase_tra->addCondition('item_id',$g->model->id);
			$purchase_tra_qty = ($purchase_tra->sum('qty')->getOne())?:0;

			$g->current_row_html[$f]=$purchase_tra_qty;
		});

		$grid->addMethod('format_purchasereturn',function($g,$f){
			$purchase_tra = $g->add('Model_Stock_Transaction',array('table_alias'=>'xt'));
			$item_j=$purchase_tra->join('stock_items','item_id');
			$purchase_tra->addCondition('branch_id',$g->api->currentBranch->id);
			$purchase_tra->addCondition('created_at','<',$g->api->nextDate($_GET['to_date']?:$g->api->now));
			$purchase_tra->addCondition('transaction_type','PurchaseReturn');
			$purchase_tra->addCondition('item_id',$g->model->id);
			$purchase_tra_qty = ($purchase_tra->sum('qty')->getOne())?:0;

			$g->current_row_html[$f]=$purchase_tra_qty;
		});

		$grid->addMethod('format_transferto',function($g,$f){
			$tra_model = $g->add('Model_Stock_Transaction',array('table_alias'=>'xt'));
			$item_j=$tra_model->join('stock_items','item_id');
			$tra_model->addCondition('created_at','<',$g->api->nextDate($_GET['to_date']?:$g->api->now));
			$tra_model->addCondition('transaction_type','Transfer');
			$tra_model->addCondition('item_id',$g->model->id);
			$tra_model->addCondition('to_branch_id','<>',$g->api->currentBranch->id);
			$tra_model->addCondition('branch_id',$g->api->currentBranch->id);
			$tra_model->addCondition('from_container_id','<>',$this->used_container_id);
			$tra_model->addCondition('to_container_id','<>',$this->used_container_id);

			$tra_model_qty = ($tra_model->sum('qty')->getOne())?:0;
			$g->current_row_html[$f]=$tra_model_qty;
		});

		$grid->addMethod('format_transferfrom',function($g,$f){
			$tra_model = $g->add('Model_Stock_Transaction',array('table_alias'=>'xt'));
			$item_j = $tra_model->join('stock_items','item_id');
			$tra_model->addCondition('created_at','<',$g->api->nextDate($_GET['to_date']?:$g->api->now));
			$tra_model->addCondition('transaction_type','Transfer');
			$tra_model->addCondition('item_id',$g->model->id);
			$tra_model->addCondition('to_branch_id',$g->api->currentBranch->id);
			$tra_model->addCondition('from_container_id','<>',$this->used_container_id);
			$tra_model->addCondition('to_container_id','<>',$this->used_container_id);

			$tra_model_from_qty = ($tra_model->sum('qty')->getOne())?:0;
			$g->current_row_html[$f]=$tra_model_from_qty;
		});

		$grid->addMethod('format_issue',function($g,$f){
			$tra_model = $g->add('Model_Stock_Transaction',array('table_alias'=>'xt'));
			$item_j=$tra_model->join('stock_items','item_id');
			$tra_model->addCondition('created_at','<',$g->api->nextDate($_GET['to_date']?:$g->api->now));
			$tra_model->addCondition('branch_id',$g->api->currentBranch->id);
			$tra_model->addCondition('item_id',$g->model->id);
			$tra_model->addCondition('transaction_type','Issue');

			$tra_model_qty = ($tra_model->sum('qty')->getOne())?:0;
			$g->current_row_html[$f]=$tra_model_qty;
			
		});

		$grid->addMethod('format_consume',function($g,$f){
			$tra_model = $g->add('Model_Stock_Transaction',array('table_alias'=>'xt'));
			$item_j=$tra_model->join('stock_items','item_id');
			$tra_model->addCondition('created_at','<',$g->api->nextDate($_GET['to_date']?:$g->api->now));
			$tra_model->addCondition('branch_id',$g->api->currentBranch->id);
			$tra_model->addCondition('item_id',$g->model->id);
			$tra_model->addCondition('transaction_type','Consume');

			$tra_model_qty = ($tra_model->sum('qty')->getOne())?:0;
			$g->current_row_html[$f]=$tra_model_qty;	
		});

		$grid->addMethod('format_submit',function($g,$f){
			$tra_model = $g->add('Model_Stock_Transaction',array('table_alias'=>'xt'));
			$item_j=$tra_model->join('stock_items','item_id');
			$tra_model->addCondition('created_at','<',$g->api->nextDate($_GET['to_date']?:$g->api->now));
			$tra_model->addCondition('branch_id',$g->api->currentBranch->id);
			$tra_model->addCondition('item_id',$g->model->id);
			$tra_model->addCondition('transaction_type','Submit');

			$tra_model_qty = ($tra_model->sum('qty')->getOne())?:0;
			$g->current_row_html[$f]=$tra_model_qty;	
		});

		$grid->addMethod('format_usedsubmit',function($g,$f){
			$tra_model = $g->add('Model_Stock_Transaction',array('table_alias'=>'xus'));
			$item_j=$tra_model->join('stock_items','item_id');
			$tra_model->addCondition('created_at','<',$g->api->nextDate($_GET['to_date']?:$g->api->now));
			$tra_model->addCondition('branch_id',$g->api->currentBranch->id);
			$tra_model->addCondition('item_id',$g->model->id);
			$tra_model->addCondition('transaction_type','UsedSubmit');

			$tra_model_qty = ($tra_model->sum('qty')->getOne())?:0;
			$g->current_row_html[$f]=$tra_model_qty;
		});

		$grid->addMethod('format_dead',function($g,$f){
			$tra_model = $g->add('Model_Stock_Transaction',array('table_alias'=>'xt'));
			$item_j=$tra_model->join('stock_items','item_id');
			$tra_model->addCondition('created_at','<',$g->api->nextDate($_GET['to_date']?:$g->api->now));
			$tra_model->addCondition('branch_id',$g->api->currentBranch->id);
			$tra_model->addCondition('item_id',$g->model->id);
			$tra_model->addCondition('transaction_type','DeadSubmit');

			$tra_model_qty = ($tra_model->sum('qty')->getOne())?:0;
			$g->current_row_html[$f]=$tra_model_qty;	
		});

		$grid->addMethod('format_deadsold',function($g,$f){
			$tra_model = $g->add('Model_Stock_Transaction',array('table_alias'=>'xt'));
			$item_j=$tra_model->join('stock_items','item_id');
			$tra_model->addCondition('created_at','<',$g->api->nextDate($_GET['to_date']?:$g->api->now));
			$tra_model->addCondition('branch_id',$g->api->currentBranch->id);
			$tra_model->addCondition('item_id',$g->model->id);
			$tra_model->addCondition('transaction_type','DeadSold');

			$tra_model_qty = ($tra_model->sum('qty')->getOne())?:0;
			$g->current_row_html[$f]=$tra_model_qty;	
		});

		$grid->addMethod('format_avgrate',function($g,$f){
			$g->current_row_html[$f]=$g->model->getAvgRate($_GET['to_date']?:$g->api->now);			
		});

		$grid->addMethod('format_balance',function($g,$f)use($self){
			$g->current_row_html[$f]=$g->model->getQty($_GET['to_date']?:$g->api->now,$self->api->currentBranch->id);
		});

		$grid->addMethod('format_amount',function($g,$f){
			$g->current_row_html[$f]=$g->model->amount($_GET['to_date']?:$g->api->now);
		});
		
		$grid->setModel($item_model);

		$grid->addColumn('openning','openning');
		$grid->addColumn('purchase','purchase');
		$grid->addColumn('purchasereturn','purchase_return');
		$grid->addColumn('transferto','transfer_out');
		$grid->addColumn('transferfrom','transfer_in');
		$grid->addColumn('issue','issue');
		$grid->addColumn('consume','consume');
		$grid->addColumn('submit','submit');
		$grid->addColumn('usedsubmit','used_submit');
		$grid->addColumn('dead','dead');
		$grid->addColumn('deadsold','dead_sold');
		$grid->addColumn('avgrate,money','avg_rate');
		$grid->addColumn('balance','balance');
		$grid->addColumn('amount','amount');
		$grid->removeColumn('row');
		$grid->removeColumn('branch');
		// $grid->removeColumn('category');
		$grid->removeColumn('is_consumable');
		$grid->removeColumn('is_issueable');
		$grid->removeColumn('is_fixedassets');
		$grid->removeColumn('is_active');
		$grid->removeColumn('is_active');
		$grid->removeColumn('container');
		$grid->removeColumn('description');

		if($form->isSubmitted()){
			$grid->js()->reload(array('to_date'=>$form['to_date'],'filter'=>1))->execute();	
		}	

		$grid->addPaginator(50);

	}
}