<?php

class page_stock_reports_itemwisestaff extends Page{
	function init(){
		parent::init();

		$form = $this->add('Form');
		$item_field = $form->addField('dropdown','item')->validateNotNull()->setEmptyText('Please Select');
		$item_model = $this->add('Model_Stock_Item');
		$item_field->setModel($item_model);

		$form->addField('DatePicker','from_date');
		$form->addField('DatePicker','to_date');
		$form->addSubmit('GET LIST');

		$v = $this->add('View');
		$item_name = "All";
		$msg = "Item Wise Staff Report";
		$msg_view = $v->add('View_Info')->setStyle(array('padding'=>'2px','margin'=>'5px 0 5px 0'));

		$grid=$v->add('Grid_AccountsBase');
		$grid->addSno();
		
		$member_model = $this->add('Model_Stock_Member');
		$member_model->addCondition('type','<>','Supplier');			
		$member_model->addCondition('branch_id',$this->api->currentBranch->id);
		
		if($_GET['filter']){
			$member_tran_j = $member_model->join('stock_transactions.member_id','id');
			$member_tran_j->addField('item_id');
			$member_tran_j->addField('created_at');
			$member_tran_j->addField('transaction_type');
			$member_model->_dsql()->group('id');
			$member_model->tryLoadAny();
			
			$grid->opening = 0;
			$grid->issue = 0;
			$grid->consume = 0;
			$grid->submit = 0;
			$grid->deadsubmit = 0;
			$grid->balance = 0;
			$grid->avgrate = 0;
			//Openning Stock of staff acording to item
			$grid->addMethod('format_opening',function($g,$f){
				//throw new Exception("member_id".$g->model['id']."i=".$_GET['item']."from=".$_GET['from_date']);
				$qty = $g->model->getOpeningQty($g->model['id'],$_GET['item'],$_GET['from_date']);
				$g->current_row[$f] = $qty;
				$g->opening = $qty;
			});

			//Issue 
			$grid->addMethod('format_issue',function($g,$f){
				$qty = $g->model->getQty($g->model['id'],$_GET['item'],$_GET['from_date'],$_GET['to_date'],"Issue");
				$g->current_row[$f] = $qty;
				$g->issue = $qty;
			});

			//CONSUME
			$grid->addMethod('format_consume',function($g,$f){
				$qty = $g->model->getQty($g->model['id'],$_GET['item'],$_GET['from_date'],$_GET['to_date'],"Consume");
				$g->current_row[$f] = $qty;
				$g->consume = $qty;
			});

			//SUBMIT
			$grid->addMethod('format_submit',function($g,$f){
				$qty = $g->model->getQty($g->model['id'],$_GET['item'],$_GET['from_date'],$_GET['to_date'],"Submit");
				$g->current_row[$f] = $qty;
				$g->submit = $qty;
			});

			//DEAD
			$grid->addMethod('format_dead',function($g,$f){
				$qty = $g->model->getQty($g->model['id'],$_GET['item'],$_GET['from_date'],$_GET['to_date'],"DeadSubmit");
				$g->current_row[$f] = $qty;
				$g->deadsubmit = $qty;
			});

			//BALANCE
			$grid->addMethod('format_balance',function($g,$f){
				// if($g->model['transaction_type']=='Consume')
				// 	$g->current_row[$f] = $g->balance = $g->consume;
				// else
					$g->balance = ( $g->current_row['opening'] + $g->current_row['issue'] ) - ( $g->current_row['submit'] + $g->current_row['dead']);
					$g->current_row_html[$f] = '<div data-balance="'.$g->balance.'" class="stock-balance">'.$g->balance.'</div>';
			});

			//AVG RATE
			$grid->addMethod('format_avgrate',function($g,$f){
				$item = $g->add('Model_Stock_Item');
				$item->load($_GET['item']);
				$g->avgrate = $item->getAvgRate($_GET['to_date']);
				$g->current_row[$f] = $g->avgrate;
			});

			//AMOUNT
			$grid->addMethod('format_amount',function($g,$f){
				$g->current_row[$f] = $g->current_row['balance'] * $g->current_row['avg_rate'];
			});

			$grid->setModel($member_model);
			
			//Add method to column in Grid
			$grid->addColumn('opening','opening');
			$grid->addColumn('issue','issue');
			$grid->addColumn('consume','consume');
			$grid->addColumn('submit','submit');
			$grid->addColumn('dead','dead');
			$grid->addColumn('balance','balance');
			$grid->addColumn('avgrate,money','avg_rate');
			$grid->addColumn('amount,money','amount');

			//Display MSG
			$item_select_model = $this->add('Model_Stock_Item')->load($_GET['item']);
			$item_name = $item_select_model['name'];
			$msg ="Item ( ".$item_name." ) Wise Staff Report From Date: ".$_GET['from_date']." To Date: ".$_GET['to_date'];
			
			$grid->js(true)->_selector('.stock-balance[data-balance="0"]')->closest('tr')->hide();
		}else{
			$member_model->addCondition('id',-1);
			$grid->setModel($member_model);
		}

		//Move Grid Column
		if($_GET['filter']){
			$grid->addOrder()->move('opening','after','name')->now();
			$grid->addOrder()->move('type','after','s_no')->now();
			$grid->addOrder()->move('issue','after','opening')->now();
			$grid->addOrder()->move('consume','after','issue')->now();
			$grid->addOrder()->move('submit','after','consume')->now();
			$grid->addOrder()->move('dead','after','submit')->now();
			$grid->addOrder()->move('balance','after','dead')->now();
			$grid->addOrder()->move('avg_rate','after','balance')->now();
			$grid->addOrder()->move('amount','after','avg_rate')->now();
		}
		//Removing Column From the Grid
		$grid->removeColumn('branch');
		$grid->removeColumn('address');
		$grid->removeColumn('ph_no');
		$grid->removeColumn('is_active');
		$grid->removeColumn('item_id');
		$grid->removeColumn('created_at');
		$grid->removeColumn('transaction_type');

		$msg_view->set($msg);
		if($form->isSubmitted()){
			$v->js()->reload(array('item'=>$form['item'],'from_date'=>$form['from_date']?:0,'to_date'=>$form['to_date']?:0,'filter'=>1))->execute();
		}


	}
}