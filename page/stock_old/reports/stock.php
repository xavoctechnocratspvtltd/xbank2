<?php

class page_stock_reports_stock extends Page {
	function init(){
		parent::init();

		$form=$this->add('Form');
		$form->addField('DatePicker','to_date','As On');
		$form->addSubmit('GET LIST');

		$item=$this->add('Model_Stock_Item');
		$item_j=$item->join('stock_transactions.item_id','id');
		$item_j->addField('created_at');

		$grid=$this->add('Grid_AccountsBase');

		$grid->addSno();
		

		$grid->addMethod('format_openning',function($g,$f){
			$openning_tra = $g->add('Model_Stock_Transaction',array('table_alias'=>'xt'));
			$item_j=$openning_tra->join('stock_items','item_id');
			$openning_tra->addCondition('created_at','<',$g->api->nextDate($_GET['to_date']?:$g->api->now));
			$openning_tra->addCondition('transaction_type','Openning');
			$openning_tra->addCondition('item_id',$g->model->id);
			$openning_tra_qty = ($openning_tra->sum('qty')->getOne())?:0;

			$g->current_row_html[$f]=$openning_tra_qty;
		});

		$grid->addMethod('format_purchase',function($g,$f){
			$purchase_tra = $g->add('Model_Stock_Transaction',array('table_alias'=>'xt'));
			$item_j=$purchase_tra->join('stock_items','item_id');
			$purchase_tra->addCondition('created_at','<',$g->api->nextDate($_GET['to_date']?:$g->api->now));
			$purchase_tra->addCondition('transaction_type','Purchase');
			$purchase_tra->addCondition('item_id',$g->model->id);
			$purchase_tra_qty = ($purchase_tra->sum('qty')->getOne())?:0;

			$g->current_row_html[$f]=$purchase_tra_qty;
		});


		$grid->addMethod('format_from',function($g,$f){
			$received_from_tra = $g->add('Model_Stock_Transaction',array('table_alias'=>'xt'));
			$item_j=$received_from_tra->join('stock_items','item_id');
			$received_from_tra->addCondition('created_at','<',$g->api->nextDate($_GET['to_date']?:$g->api->now));
			$received_from_tra->addCondition('transaction_type','Purchase');
			$received_from_tra->addCondition('item_id',$g->model->id);
			$received_from_tra->addCondition('branch_id',$g->api->currentBranch->id);
			$received_from_tra_qty = ($received_from_tra->sum('qty')->getOne())?:0;

			$g->current_row_html[$f]=$received_from_tra_qty;
		});

		$grid->addMethod('format_to',function($g,$f){
			$transfer_to_tra = $g->add('Model_Stock_Transaction',array('table_alias'=>'xt'));
			$item_j=$transfer_to_tra->join('stock_items','item_id');
			$transfer_to_tra->addCondition('created_at','<',$g->api->nextDate($_GET['to_date']?:$g->api->now));
			$transfer_to_tra->addCondition('transaction_type','Transfer');
			$transfer_to_tra->addCondition('item_id',$g->model->id);
			$transfer_to_tra->addCondition('to_branch_id',$g->api->currentBranch->id);
			$transfer_to_tra_qty = ($transfer_to_tra->sum('qty')->getOne())?:0;

			$g->current_row_html[$f]=$transfer_to_tra_qty;
		});


		$grid->addMethod('format_return',function($g,$f){
			$return_tra = $g->add('Model_Stock_Transaction',array('table_alias'=>'xt'));
			$item_j=$return_tra->join('stock_items','item_id');
			$return_tra->addCondition('created_at','<',$g->api->nextDate($_GET['to_date']?:$g->api->now));
			$return_tra->addCondition('transaction_type','PurchaseReturn');
			$return_tra->addCondition('item_id',$g->model->id);
			$return_tra_qty = ($return_tra->sum('qty')->getOne())?:0;

			$g->current_row_html[$f]=$return_tra_qty;
		});


		$grid->addMethod('format_consumeissue',function($g,$f){

			$g->current_row_html[$f]=$g->model->getIssueConsume($_GET['to_date']?:$g->api->now);
		});


		$grid->addMethod('format_stock',function($g,$f){
			$g->current_row_html[$f]=$g->model->getQty($_GET['to_date']?:$g->api->now);
		});

		$grid->addMethod('format_avgrate',function($g,$f){
			$g->current_row_html[$f]=round($g->model->getAvgRate($_GET['to_date']?:$g->api->now),2);
		});


		$grid->addMethod('format_amount',function($g,$f){
			$g->current_row_html[$f]=$g->model->amount($_GET['to_date']?:$g->api->now);
		});

		if($_GET['filter']){
			if($_GET['to_date'])
				$item->addCondition('created_at','<=',$_GET['to_date']);
		}


		$grid->setModel($item);

		$grid->addColumn('openning','openning');
		$grid->addColumn('purchase','purchase');
		$grid->addColumn('from','received_from_branch');
		$grid->addColumn('to','transfer_to_branch');
		$grid->addColumn('consumeissue','consume_issue');
		$grid->addColumn('return','return');
		$grid->addColumn('stock','stock_in_hand');
		$grid->addColumn('avgrate,money','avg_rate');
		$grid->addColumn('amount','amount');
		$grid->removeColumn('row');
		$grid->removeColumn('category');
		$grid->removeColumn('is_consumable');
		$grid->removeColumn('is_issueable');
		$grid->removeColumn('is_fixedassets');
		$grid->removeColumn('container');
		$grid->removeColumn('description');


		if($form->isSubmitted()){
			$grid->js()->reload(array('to_date'=>$form['to_date'],'filter'=>1))->execute();
		}

		


		

	}
}