<?php

class View_StockMember_Consume extends View {
	function init(){
		parent::init();
		
		
		$transaction=$this->add('Model_Stock_Transaction');
		$transaction_j_item=$transaction->join('stock_items','item_id');
		$transaction_j_item->addField('is_consumable');
		$transaction->addCondition('is_consumable',true);
		$transaction->addCondition('transaction_type','Consume');
		  
		$grid=$this->add('Grid_AccountsBase');
		$member_model=$this->add('Model_Stock_Member');
		
		if($_GET['filter']){
						
			if($_GET['member']){
				$member_model->load($this->member);
				$transaction->addCondition('member_id',$this->member);
			}

			if($this->from_date)
				$transaction->addCondition('created_at','>=',$this->from_date);
			if($this->to_date)
				$transaction->addCondition('created_at','<=',$this->to_date);

		}else
			$transaction->addCondition('id',-1);

		// $openning_bal=$staff_model->getQty($_GET['from_date']);
		$grid->setModel($transaction,array('item','qty','created_at'));	
		$grid->addColumn('text','DR');
		$grid->addColumn('text','CR');
		$grid->removeColumn('qty');
		
		$grid->addSno();

		$grid->addHook('formatRow',function($grid){
			if(in_array($grid->model['transaction_type'],array('Purchase','Submit','Transfer','Openning','DeadSubmit'))){
				$fill='DR';
				$no_fill='CR';
			}else{
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

		// $grid->addOpeningBalance(0,'DR',array('narration'=>'Openning Balance'),'DR');
		$grid->addCurrentBalanceInEachRow('Balance','last','CR','CR','DR');

	}
}