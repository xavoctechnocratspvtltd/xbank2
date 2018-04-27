<?php

class page_stock_reports_itemtransaction extends Page{

	function init(){
		parent::init();

		$form = $this->add('Form');
		$item_field = $form->addField('dropdown','item')->validateNotNull()->setEmptyText('Please Select');
		$transaction_field = $form->addField('dropdown','transaction_type')->setValueList(array('Issue'=>'Issue','Purchase'=>'Purchase','Consume'=>'Consume','Submit'=>'Submit','UsedSubmit'=>'UsedSubmit','PurchaseReturn'=>'PurchaseReturn','DeadSubmit'=>'DeadSubmit','TransferIn'=>'TransferIn','TransferOut'=>'TransferOut','Sold'=>'Sold','DeadSold'=>'DeadSold'))->setEmptyText('Please Select');
		$form->addField('DatePicker','from_date');
		$form->addField('DatePicker','to_date');

		$item = $this->add('Model_Stock_Item');
		$item_field->setModel($item);

		$form->addSubmit('GET LIST');
		$v = $this->add('View');
		$item_name = "All";
		$transaction_name = "All";
		$msg = "Item Wise Staff Report";
		$msg_view = $v->add('View_Info')->setStyle(array('padding'=>'2px','margin'=>'5px 0 5px 0'));

		$grid=$v->add('Grid_AccountsBase');		
		$grid->addSno();
		$transaction_model = $this->add('Model_Stock_Transaction');
		$transaction_model->addCondition($transaction_model->dsql()->orExpr()->where('branch_id',$this->api->currentBranch->id)->where('to_branch_id',$this->api->currentBranch->id));

		if($_GET['filter']){
			$transaction_model->addCondition('item_id',$_GET['item']);
			$transaction_model->addCondition('transaction_type','<>',array('Openning','move'));
			if($_GET['from_date'])
				$transaction_model->addCondition('created_at','>=',$_GET['from_date']);
			if($_GET['to_date'])
				$transaction_model->addCondition('created_at','<',$this->app->nextDate($_GET['to_date']));
			
			if($_GET['transaction_type']){
				if($_GET['transaction_type'] == "TransferIn"){
					$transaction_model->addCondition('transaction_type','Transfer');
					$transaction_model->addCondition('to_branch_id',$this->api->currentBranch->id);
					// $transaction_model->addCondition('branch_id','<>',$this->api->currentBranch->id);
					// throw new \Exception($transaction_model->count()->getOne());

				}elseif($_GET['transaction_type'] == "TransferOut"){
					$transaction_model->addCondition('transaction_type','Transfer');
					// $transaction_model->addCondition('branch_id','<>',$this->api->currentBranch->id);
					$transaction_model->addCondition('branch_id',$this->api->currentBranch->id);
				}else{
					$transaction_model->addCondition('transaction_type',$_GET['transaction_type']);	
					$transaction_model->addCondition('branch_id',$this->api->currentBranch->id);
				}
				
				$transaction_name = $transaction_model['transaction_type'];	

			}

			// $transaction_model->_dsql()->group('transaction_type');
			// $transaction_model->setOrder('transaction_type','desc');
			
			$grid->addMethod('format_opening',function($g,$f){
				//return opening stock + from_date opening
				$qty = $g->model->getTransactionOpeningQty($_GET['item'],$g->model['transaction_type'],$_GET['from_date']?:$g->model['created_at'],$_GET['to_date']);
				$g->current_row_html[$f]= $qty;
			});

			$grid->addMethod('format_qty',function($g,$f){
				$item_model = $g->add('Model_Stock_Item');
				$item_model->load($_GET['item']);
				$g->current_row[$f] = $item_model->getQty($_GET['to_date']);
			});

			$grid->addMethod('format_avgrate',function($g,$f){
				$item_model = $g->add('Model_Stock_Item');
				$item_model->load($_GET['item']);
				$g->current_row[$f] = $item_model->getAvgRate($_GET['to_date']);
				
			});

			$grid->addMethod('format_totalamount',function($g,$f){
				// throw new Exception("Error Processing Request".$g->current_row['avgrate']);
				$g->current_row[$f] = (int)$g->current_row['qty'] * (int)$g->current_row['avg_rate'];				 
			});			


			$grid->addColumn('opening','opening');
			$grid->addColumn('qty','qty');
			$grid->addColumn('avgrate,money','avg_rate');
			$grid->addColumn('totalamount,money','total_amount');

			//Display MSG
			$selected_item_model = $this->add('Model_Stock_Item')->load($_GET['item']);	
			$item_name = $selected_item_model['name'];
			$msg ="Item ( ".$item_name." ) Wise Transaction ( ".$transaction_name." ) Report From Date: ".$_GET['from_date']." To Date: ".$_GET['to_date'];
		}else
			$transaction_model->addCondition('id',-1);

		$grid->setModel($transaction_model);
		
		if($_GET['transaction_type'] !== "TransferOut"){
			$grid->removeColumn('branch');
			$grid->removeColumn('to_branch_id');
		}else{
			$grid->addFormatter('to_branch_id','to');
    		$grid->addMethod('format_to',function($g,$f){
    			$g->current_row[$f] = $g->add('Model_Branch')->addCondition('id',$g->current_row[$f])->tryLoadAny()->get('name');
    		});
		}

		if($_GET['transaction_type'] !=="Issue" or $_GET['transaction_type'] !=="Submit" or $_GET['transaction_type'] !=="Consume"){
			$grid->removeColumn('member');
		}
			
		$grid->removeColumn('item');
		$grid->removeColumn('rate');
		$grid->removeColumn('amount');
		// $grid->removeColumn('narration');
		// $grid->removeColumn('created_at');
		$grid->removeColumn('issue_date');
		$grid->removeColumn('submit_date');
		$grid->removeColumn('to_container');
		$grid->removeColumn('to_row');
		$grid->removeColumn('from_container');
		$grid->removeColumn('from_row');


		if($_GET['filter'] and !$_GET['transaction_type']){
			$grid->addOrder()->move('opening','after','transaction_type')->now();
			$grid->addOrder()->move('qty','after','opening')->now();	
			$grid->addOrder()->move('avg_rate','after','qty')->now();	
			$grid->addOrder()->move('total_amount','after','avg_rate')->now();	
		}
		
		$msg_view->set($msg);
		if($form->isSubmitted()){
			$v->js()->reload(array('item'=>$form['item'],'transaction_type'=>$form['transaction_type'],'from_date'=>$form['from_date']?:0,'to_date'=>$form['to_date']?:0,'filter'=>1))->execute();
		}

	}
}