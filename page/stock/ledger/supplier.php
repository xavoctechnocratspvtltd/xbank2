<?php

class page_stock_ledger_supplier extends Page {
	function init(){
		parent::init();

		$openning_bal = 0;

		$form=$this->add('Form');
		$item_field=$form->addField('dropdown','item')->setEmptyText('Please Select');
		$item_field->setModel('Stock_Item');

		$supplier_field=$form->addField('dropdown','supplier')->validateNotNull()->setEmptyText('Please Select');
		$supplier_model = $this->add('Model_Stock_Supplier');
		$supplier_field->setModel($supplier_model);

		$form->addField('DatePicker','from_date');
		$form->addField('DatePicker','to_date');
		$form->addSubmit('GET');

		$transaction=$this->add('Model_Stock_Transaction');
		$transaction_j_item=$transaction->join('stock_items','item_id');
		if($_GET['item'])
			$transaction->addCondition('item_id',$_GET['item']);
		$transaction->addCondition('transaction_type',array('Purchase','PurchaseReturn'));
		$transaction->addCondition('member_id',$_GET['supplier']);

		$member_model = $this->add('Model_Stock_Member');
		if($_GET['filter']){				
			if($_GET['supplier']){
				// throw $this->exception("Member_id".$this->member.$_GET['type']);
				$transaction->addCondition('member_id',$_GET['supplier']);				
			}
			if($_GET['item']){
				$member_model->load($_GET['supplier']);
				$openning_bal=$member_model->getSupplierOpeningQty($member_model->id,$_GET['item'],$_GET['from_date']);
			}
			if($_GET['from_date'])
				$transaction->addCondition('created_at','>=',$_GET['from_date']);
			if($_GET['to_date'])
				$transaction->addCondition('created_at','<=',$_GET['to_date']);
		}else
			$transaction->addCondition('id',-1);
		

		$grid=$this->add('Grid_AccountsBase');
		$grid->addSno();
		$transaction->setOrder('created_at','asc');	
		$grid->setModel($transaction,array('item','qty','created_at'));
			
		// if(!$_GET['item'])	
			// $grid->addColumn('text','OpeningBal');
		$grid->addColumn('text','DR');
		$grid->addColumn('text','CR');
		$grid->removeColumn('qty');

		$grid->addHook('formatRow',function($grid){
			if(in_array($grid->model['transaction_type'],array('Purchase'))){
				$fill='DR';
				$no_fill='CR';
			}else{
				$fill='CR';
				$no_fill='DR';
			}

			if($grid->model['transaction_type']=='PurchaseReturn'){
				$fill='CR';
				$no_fill='DR';
			}
				$grid->current_row[$fill] = $grid->model['qty'];
				$grid->current_row[$no_fill] ='';
		});

		if($_GET['item']){
			$grid->addOpeningBalance($openning_bal,'DR',array('created_at'=>'Openning Balance'),'DR');
			$grid->addCurrentBalanceInEachRow('Balance','last','CR','CR','DR');
		}
		else{
			$grid->addMethod('format_balance',function($grid,$field){
				$m_temp = $grid->add('Model_Stock_Member');
				$grid->current_row[$field]= $m_temp->getSupplierOpeningQty($grid->model['member_id'],$grid->model['item_id'],$grid->api->nextDate($grid->model['created_at']));
			});
			$grid->addColumn('balance','balance');
			
		}	

		if($form->isSubmitted()){
			$grid->js()->reload(array('item'=>$form['item'],'supplier'=>$form['supplier'],'from_date'=>$form['from_date']?:0,'to_date'=>$form['to_date']?:0,'filter'=>1))->execute();
		}

	}
}	