<?php

class View_StockMember_Report extends View {
	
	function init(){
		parent::init();

		// $this->add('View_Info')->set('Report');

		$tran_model = $this->add('Model_Stock_Transaction');
		$tran_model_j = $tran_model->join('stock_items','item_id');
		$tran_model->addCondition('member_id',$this->member);
		if($this->item)
			$tran_model->addCondition('item_id',$this->item);
		$tran_model->_dsql()->group('item_id');
		$tran_model->_dsql()->group('member_id');

		$grid=$this->add('Grid_AccountsBase');
		$grid->addSno();
		// array('member'=>$_GET['staff'],'item'=>$_GET['item'],'from_date'=>$_GET['from_date'],'to_date'=>$form['to_date'],'filter'=>$_GET['filter'],'type'=>'Staff');
		$grid->addMethod('format_openning',function($g,$f){	
			$member_model = $g->add('Model_Stock_Member');
			$member_model->load($g->model['member_id']);
			$qty = $member_model->getOpeningQty($g->model['member_id'],$g->model['item_id'],$this->from_date?:'1970-01-01');						
			$g->current_row_html[$f] = $qty;			
			// throw new Exception($g->model['member_id']."i=".$g->model['item_id']."as_on".$this->from_date);	
		});

		$grid->addMethod('format_issue',function($g,$f){
			$member_model = $g->add('Model_Stock_Member');
			$member_model->load($g->model['member_id']);
			$qty = $member_model->getQty($g->model['member_id'],$g->model['item_id'],$this->from_date?:'1970-01-01',$this->to_date,'Issue');						
			$g->current_row_html[$f] = $qty;
		});

		$grid->addMethod('format_consume',function($g,$f){
			$member_model = $g->add('Model_Stock_Member');
			$member_model->load($g->model['member_id']);
			$qty = $member_model->getQty($g->model['member_id'],$g->model['item_id'],$this->from_date?:'1970-01-01',$this->to_date,'Consume');						
			$g->current_row_html[$f] = $qty;
		});

		$grid->addMethod('format_submit',function($g,$f){
			$member_model = $g->add('Model_Stock_Member');
			$member_model->load($g->model['member_id']);
			$qty = $member_model->getQty($g->model['member_id'],$g->model['item_id'],$this->from_date?:'1970-01-01',$this->to_date,'Submit');						
			$g->current_row_html[$f] = $qty;
		});

		$grid->addMethod('format_deadsubmit',function($g,$f){
			$member_model = $g->add('Model_Stock_Member');
			$member_model->load($g->model['member_id']);
			$qty = $member_model->getQty($g->model['member_id'],$g->model['item_id'],$this->from_date?:'1970-01-01',$this->to_date,'DeadSubmit');						
			$g->current_row_html[$f] = $qty;
		});

		$grid->addMethod('format_balance',function($g,$f){
				
		});

		$grid->addMethod('format_avgrate',function($g,$f){
			// $g->current_row_html[$f]=$g->model->getAvgRate($_GET['to_date']?:$g->api->now);			
		});

		$grid->addMethod('format_amount',function($g,$f){
			// $g->current_row_html[$f]=$g->model->amount($_GET['to_date']?:$g->api->now);
		});

		$grid->setModel($tran_model);

		$grid->addColumn('openning','openning');
		$grid->addColumn('issue','issue');
		$grid->addColumn('consume','consume');
		$grid->addColumn('submit','submit');
		$grid->addColumn('deadsubmit','dead');
		$grid->addColumn('balance','balance');
		// $grid->addColumn('avgrate,money','avg_rate');
		// $grid->addColumn('amount','amount');
		$grid->removeColumn('to_branch');
		$grid->removeColumn('rate');
		$grid->removeColumn('qty');
		$grid->removeColumn('amount');
		$grid->removeColumn('narration');
		$grid->removeColumn('created_at');
		$grid->removeColumn('issue_date');
		$grid->removeColumn('submit_date');
		$grid->removeColumn('transaction_type');
		$grid->removeColumn('branch');
	}
}