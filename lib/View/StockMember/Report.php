<?php

class View_StockMember_Report extends View {
	
	function init(){
		parent::init();

		// $this->add('View_Info')->set('Report');

		$tran_model = $this->add('Model_Stock_Transaction');
		$tran_model_j = $tran_model->join('stock_items','item_id');
		$tran_model->addCondition('member_id',$this->member);
		if($this->to_date)
			$tran_model->addCondition('created_at','<=',$this->to_date);
		if($this->item)
			$tran_model->addCondition('item_id',$this->item);
		$tran_model->_dsql()->group('item_id');
		$tran_model->_dsql()->group('member_id');

		$grid=$this->add('Grid_AccountsBase');
		$grid->addSno();

		$grid->openning = 0;
		$grid->issue = 0;
		$grid->consume = 0;
		$grid->submit = 0;
		$grid->deadsubmit = 0;
		$grid->avgrate = 0;
		$grid->balance = 0;
		$grid->from_date = $this->from_date;
		$grid->to_date = $this->from_date;
		// array('member'=>$_GET['staff'],'item'=>$_GET['item'],'from_date'=>$_GET['from_date'],'to_date'=>$form['to_date'],'filter'=>$_GET['filter'],'type'=>'Staff');
		$grid->addMethod('format_openning',function($g,$f){	
			$member_model = $g->add('Model_Stock_Member');
			$member_model->load($g->model['member_id']);
			$qty = $member_model->getOpeningQty($g->model['member_id'],$g->model['item_id'],$g->from_date?:'1970-01-01');						
			$g->current_row_html[$f] = $qty;
			$g->openning = $qty;			
			// throw new Exception($g->model['member_id']."i=".$g->model['item_id']."as_on".$this->from_date);	
		});

		$grid->addMethod('format_issue',function($g,$f){
			$member_model = $g->add('Model_Stock_Member');
			$member_model->load($g->model['member_id']);
			$qty = $member_model->getQty($g->model['member_id'],$g->model['item_id'],$g->from_date?:'1970-01-01',$g->to_date,'Issue');						
			$g->current_row_html[$f] = $qty;
			$g->issue = $qty;
		});

		$grid->addMethod('format_consume',function($g,$f){
			$member_model = $g->add('Model_Stock_Member');
			$member_model->load($g->model['member_id']);
			$qty = $member_model->getQty($g->model['member_id'],$g->model['item_id'],$g->from_date?:'1970-01-01',$g->to_date,'Consume');						
			$g->current_row_html[$f] = $qty;
			$g->consume = $qty;
		});

		$grid->addMethod('format_submit',function($g,$f){
			$member_model = $g->add('Model_Stock_Member');
			$member_model->load($g->model['member_id']);
			$qty = $member_model->getQty($g->model['member_id'],$g->model['item_id'],$g->from_date?:'1970-01-01',$g->to_date,'Submit');						
			$g->current_row_html[$f] = $qty;
			$g->submit = $qty; 
		});

		$grid->addMethod('format_deadsubmit',function($g,$f){
			$member_model = $g->add('Model_Stock_Member');
			$member_model->load($g->model['member_id']);
			$qty = $member_model->getQty($g->model['member_id'],$g->model['item_id'],$g->from_date?:'1970-01-01',$g->to_date,'DeadSubmit');						
			$g->current_row_html[$f] = $qty;
			$g->deadsubmit = $qty;
		});

		$grid->addMethod('format_balance',function($g,$f){
			// throw new Exception();
			if($g->model['transaction_type'] == "Issue"){
				$qty = ( $g->openning + $g->issue )- ( $g->submit + $g->deadsubmit );
				$g->current_row_html[$f] = $qty; 
				$g->balance = $qty;
			}
			
			if($g->model['transaction_type'] == "Consume"){
				$g->current_row_html[$f] = $g->consume;
				$g->balance = $g->consume;
			}
				
		});

		$grid->addMethod('format_avgrate',function($g,$f){
			$item_model = $g->add('Model_Stock_Item');
			$item_model->load($g->model['item_id']);
			$qty = $item_model->getAvgRate($g->to_date?:$g->api->now);
			$g->current_row_html[$f] = $qty;
			$g->avgrate = $qty;			
		});

		$grid->addMethod('format_amount',function($g,$f){
			$g->current_row_html[$f] = $g->balance * $g->avgrate;
			$g->openning = 0;
			$g->issue = 0;
			$g->consume = 0;
			$g->submit = 0;
			$g->deadsubmit = 0;
			$g->avgrate = 0;
			$g->balance = 0;
		});

		$grid->setModel($tran_model);

		$grid->addColumn('openning','openning');
		$grid->addColumn('issue','issue');
		$grid->addColumn('consume','consume');
		$grid->addColumn('submit','submit');
		$grid->addColumn('deadsubmit','dead');
		$grid->addColumn('balance','balance');
		$grid->addColumn('avgrate,money','avg_rate');
		$grid->addColumn('amount','amount');
		$grid->addOrder()->move('amount','last')->now();

		$grid->removeColumn('to_branch_id');
		$grid->removeColumn('rate');
		$grid->removeColumn('qty');
		$grid->removeColumn('narration');
		$grid->removeColumn('created_at');
		$grid->removeColumn('issue_date');
		$grid->removeColumn('submit_date');
		$grid->removeColumn('transaction_type');
		$grid->removeColumn('branch');
		$grid->removeColumn('to_container');
		$grid->removeColumn('to_row');
		$grid->removeColumn('from_container');
		$grid->removeColumn('from_row');
		// $grid->removeColumn('amount');

	}
}