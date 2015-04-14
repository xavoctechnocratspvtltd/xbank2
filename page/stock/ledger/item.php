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
		$transaction->addCondition('transaction_type','<>',array('DeadSubmit','DeadSold','Openning'));
		
		$grid=$this->add('Grid_AccountsBase');

		$item_model=$this->add('Model_Stock_Item');

		if($_GET['filter']){						
			if($_GET['item']){
				$item_model->load($_GET['item']);
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
		$openning_bal = $item_model->getQty($_GET['from_date']?:'1970-01-01');
		$openning_bal += $item_model->getOpeningQty($_GET['item'],$_GET['from_date']?:'1970-01-01');

		$grid->setModel($transaction,array('item','narration','transaction_type','qty','created_at'));	

		$grid->addColumn('text','DR');
		$grid->addColumn('text','CR');
		// $grid->removeColumn('qty');
		
		$grid->addSno();

		$grid->addHook('formatRow',function($grid){
			if(in_array($grid->model['transaction_type'],array('Purchase','Submit','Transfer','Openning','DeadSubmit','UsedSubmit'))){
				$fill='DR';
				$no_fill='CR';
			}else{
				if($grid->model['transaction_type']=='Move')
					return;
				$fill='CR';
				$no_fill='DR';
			}

			if($grid->model['transaction_type']=='Transfer' and $grid->model['to_branch_id']!=$grid->api->currentBranch->id){
				$fill='CR';
				$no_fill='DR';
			}
				$grid->current_row[$fill] = $grid->model['qty'];
				$grid->current_row[$no_fill] ='';
		});



		$grid->addOpeningBalance($openning_bal,'DR',array('narration'=>'Openning Balance'),'DR');
		$grid->addCurrentBalanceInEachRow('Balance','last','CR','CR','DR');

		if($form->isSubmitted()){
			$grid->js()->reload(array('item'=>$form['item']?:0,'from_date'=>$form['from_date']?:0,'to_date'=>$form['to_date'],'filter'=>1))->execute();
		}

	}
}