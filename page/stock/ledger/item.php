<?php

class page_stock_ledger_item extends Page {
	function init(){
		parent::init();

		$form=$this->add('Form');
		$item_field=$form->addField('dropdown','item')->validateNotNull()->setEmptyText('Please Select');
		$item_field->setModel('Stock_Item');
		$form->addField('DatePicker','from_date');
		$form->addField('DatePicker','to_date');
		// $form->addField('CheckBox','include_dead');
		$form->addSubmit('GET');
		$transaction=$this->add('Model_Stock_Transaction');
		$transaction->addCondition('transaction_type','<>',array('DeadSubmit','DeadSold','UsedSubmit'));
		
		$grid=$this->add('Grid_AccountsBase');

		$item_model=$this->add('Model_Stock_Item');

		if($_GET['filter']){						
			if($_GET['item']){
				$item_model->load($_GET['item']);

				// $transaction->addCondition('branch_id',$this->api->currentBranch->id);
				//Add Condition for Transfer in transaction
				$transaction->addCondition($transaction->dsql()->orExpr()->where('branch_id',$this->api->current_branch->id)->where('to_branch_id',$this->api->current_branch->id));
				//transfer in not commimg 
				$transaction->addCondition('item_id',$_GET['item']);
			}
			// if(!$_GET['include_dead'])
				// $transaction->addCondition('transaction_type',)
			if($_GET['from_date'])
				$transaction->addCondition('created_at','>=',$_GET['from_date']);
			if($_GET['to_date'])
				$transaction->addCondition('created_at','<=',$_GET['to_date']);
		}else
			$transaction->addCondition('id',-1);
	
		$transaction->setOrder('created_at','asc');	
		$openning_bal = $item_model->getQty($_GET['from_date']?:'1970-01-01',$branch_id=$this->api->currentBranch->id);
		$openning_bal += $item_model->getOpeningQty($_GET['item'],$_GET['from_date']?:'1970-01-01',$branch_id=$this->api->currentBranch->id);

		$grid->setModel($transaction,array('item','narration','transaction_type','qty','created_at','to_branch_id'));	

		$grid->addColumn('text','DR');
		$grid->addColumn('text','CR');
		// $grid->removeColumn('qty');
		
		$grid->addSno();

		$grid->addHook('formatRow',function($grid){
			if($grid->model['transaction_type']=='Move')
				return;

			$fill='DR';
			$no_fill='CR';
			
			if(in_array($grid->model['transaction_type'],array('Purchase','Submit','Openning','DeadSubmit'))){
				$fill='CR';
				$no_fill='DR';
			}else{
				if($grid->model['transaction_type'] == "Transfer"){
					if($grid->model['to_branch_id'] == $grid->api->current_branch->id){
						$fill = 'CR';
						$no_fill = 'DR';
					}
				}
						
			}

			$grid->current_row[$fill] = $grid->model['qty'];
			$grid->current_row[$no_fill] ='';
		});



		$grid->addOpeningBalance($openning_bal,$openning_bal>0?'CR':'DR',array('narration'=>'Openning Balance'),'CR');
		$grid->addCurrentBalanceInEachRow('Balance','last','CR','CR','DR');

		if($form->isSubmitted()){
			$grid->js()->reload(array('item'=>$form['item']?:0,'from_date'=>$form['from_date']?:0,'to_date'=>$form['to_date']?:0,'filter'=>1))->execute();
		}

	}
}