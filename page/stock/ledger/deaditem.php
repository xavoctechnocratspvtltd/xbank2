<?php

class page_stock_ledger_deaditem extends Page{
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
		$transaction->addCondition('transaction_type',array('DeadSubmit','DeadSold'));
		$transaction->addCondition('branch_id',$this->api->currentBranch->id);
		
		$msg ="Dead Item Leadger";
		$v = $this->add('View');

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
			$msg = "Deat Item ( ".$item_model['name']." ) From Date: ".$_GET['from_date']." To date: ".$_GET['to_date'];
		}else
			$transaction->addCondition('id',-1);
			
		$v->add('View_Info')->set($msg)->setStyle(array('padding'=>'2px','margin'=>'5px 0 5px 0'));		
		$grid=$v->add('Grid_AccountsBase');

		$transaction->setOrder('created_at','asc');	
		$openning_bal=$item_model->DeadQty($_GET['from_date']?:'1970-01-01');
		$grid->setModel($transaction,array('item','narration','transaction_type','qty','created_at'));	
		
		$grid->addColumn('text','DR');
		$grid->addColumn('text','CR');		
		$grid->addSno();
		
		$grid->addHook('formatRow',function($grid){
			if(in_array($grid->model['transaction_type'],array('DeadSubmit'))){
				$fill='DR';
				$no_fill='CR';
			}else{
				$fill='CR';
				$no_fill='DR';
			}

			$grid->current_row[$fill] = $grid->model['qty'];
			$grid->current_row[$no_fill] ='';
		});

		$grid->addOpeningBalance($openning_bal,'DR',array('narration'=>'Opening Balance'),'DR');
		$grid->addCurrentBalanceInEachRow('Balance','last','CR','CR','DR');

		if($form->isSubmitted()){
			$v->js()->reload(array('item'=>$form['item']?:0,'from_date'=>$form['from_date']?:0,'to_date'=>$form['to_date']?:0,'filter'=>1))->execute();
		}
	}
}