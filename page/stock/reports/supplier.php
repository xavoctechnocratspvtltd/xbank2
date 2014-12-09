<?php

class page_stock_reports_supplier extends Page {
	function page_index(){
		parent::init();

		$form=$this->add('Form');
		$staff_field=$form->addField('dropdown','supplier')->validateNotNull()->setEmptyText('Please Select');
		$staff_field->setModel('Stock_Supplier');
		
		$item_field=$form->addField('dropdown','item')->setEmptyText('All');
		$item_model = $this->add('Model_Stock_Item');
		$item_field->setModel($item_model);
		$form->addField('DatePicker','from_date');
		$form->addField('DatePicker','to_date');

		$form->addSubmit('GET LIST');

		$v = $this->add('View');
		$item_name = "All";
		$msg = "Supplier Report";
		$msg_view = $v->add('View_Info')->set($msg)->setStyle(array('padding'=>'2px','margin'=>'5px 0 5px 0'));
		$grid=$v->add('Grid_AccountsBase');
		$grid->addSno();
		$tran_model = $this->add('Model_Stock_Transaction');


		if($_GET['filter']){				
			$tran_model_j = $tran_model->join('stock_items','item_id');
			if($_GET['supplier']){
				$supplier_model = $this->add('Model_Stock_Supplier')->load($_GET['supplier']);
				$tran_model->addCondition('member_id',$_GET['supplier']);
			}

			if($_GET['item']){
				$item_model = $this->add('Model_Stock_Item')->load($_GET['item']);
				$item_name = $item_model['name'];
				$tran_model->addCondition('item_id',$_GET['item']);
			}
			$tran_model->_dsql()->group('item_id');
			$tran_model->_dsql()->group('member_id');

			$grid->opening = 0;
			$grid->purchase = 0;
			$grid->return = 0;
			$grid->avgrate = 0;
			//OPENING BALANCE FORMATER
			$grid->addMethod('format_openning',function($g,$f){	
				$member = $g->add('Model_Stock_Member');
				$member->load($g->model['member_id']);
				$qty = $member->getSupplierOpeningQty($g->model['member_id'],$g->model['item_id'],$_GET['from_date']);				
				$g->current_row_html[$f] = $qty;
				$g->opening = $qty;
			});

			$grid->addMethod('format_purchase',function($g,$f){		
				$member = $g->add('Model_Stock_Member');
				$member->load($g->model['member_id']);
				$purchase_tra_qty = $member->getSupplierPurchaseQty($g->model['member_id'],$g->model['item_id'],$_GET['from_date'],$_GET['to_date']);
				$g->current_row_html[$f] = $purchase_tra_qty;
				$g->purchase = $purchase_tra_qty ;
			});

			$grid->addMethod('format_purchasereturn',function($g,$f){		
				$member = $g->add('Model_Stock_Member');
				$member->load($g->model['member_id']);
				$return_tra_qty = $member->getSupplierPurchaseReturnQty($g->model['member_id'],$g->model['item_id'],$_GET['from_date'],$_GET['to_date']);
				$g->current_row_html[$f] = $return_tra_qty;
				$g->return = $return_tra_qty ;
			});	

			$grid->addMethod('format_balance',function($g,$f){
				$g->current_row_html[$f] = ( $g->opening + $g->purchase - $g->return );
				$g->balance = ( $g->opening + $g->purchase - $g->return );
			});	

			$grid->addMethod('format_avgrate',function($g,$f){
				$item_model = $g->add('Model_Stock_Item');
				$item_model->load($g->model['item_id']);
				$qty = $item_model->getAvgRate($_GET['to_date']?:$g->api->now);
				$g->current_row_html[$f] = $qty;
				$g->avgrate = $qty;

			});

			$grid->addMethod('format_amount',function($g,$f){
				$g->current_row_html[$f] = $g->avgrate * $g->balance;
				$g->opening = 0;
				$g->purchase = 0;
				$g->return = 0;
				$g->avgrate = 0;
			});
			//ADDING FORMTARE COLUMN IN GRID
			$grid->addColumn('openning','opening');
			$grid->addColumn('purchase','purchase');
			$grid->addColumn('purchasereturn','purchase_return');
			$grid->addColumn('balance','balance');
			$grid->addColumn('avgrate','avg_rate');
			$grid->addColumn('amount','total_amount');

			//Display Massage			
			$msg = "Supplier ( ".$supplier_model['name']." ) Item ( ".$item_name." ) Report From Date: ".$_GET['from_date']." To Date: ".$_GET['to_date'];
		}else			
			$tran_model->addCondition('id',-1);

		$msg_view->set($msg);		
		$grid->setModel($tran_model);

		if($_GET['filter']){
			$grid->addOrder()->move('opening','after','member')->now();
			$grid->addOrder()->move('purchase','after','opening')->now();
			$grid->addOrder()->move('purchase_return','after','purchase')->now();
			$grid->addOrder()->move('balance','after','purchase_return')->now();
			$grid->addOrder()->move('avg_rate','after','balance')->now();
			$grid->addOrder()->move('total_amount','after','avg_rate')->now();
		}

		//REMOVING EXTRA COLUMN FROM GRID
		$grid->removeColumn('branch');
		$grid->removeColumn('narration');
		$grid->removeColumn('to_branch_id');
		$grid->removeColumn('created_at');
		$grid->removeColumn('issue_date');
		$grid->removeColumn('submit_date');
		$grid->removeColumn('transaction_type');
		$grid->removeColumn('rate');
		$grid->removeColumn('qty');
		$grid->removeColumn('amount');
		$grid->removeColumn('to_container');
		$grid->removeColumn('to_row');
		$grid->removeColumn('from_container');
		$grid->removeColumn('from_row');
		
		if($form->isSubmitted()){
			$v->js()->reload(array('supplier'=>$form['supplier'],'item'=>$form['item'],'from_date'=>$form['from_date']?:'1970-01-01','to_date'=>$form['to_date']?:$this->api->now,'filter'=>1))->execute();
		}
	
	}

}