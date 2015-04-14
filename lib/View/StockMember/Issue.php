<?php

class View_StockMember_Issue extends View {
	function init(){
		parent::init();
		// if(!$_GET['filter'])
			// throw $this->exception('Something is wrong');
		//
		$this->api->StickyGet('item');
		$openning_bal = 0;	
		$transaction=$this->add('Model_Stock_Transaction');
		$transaction_j_item=$transaction->join('stock_items','item_id');
		$transaction_j_item->addField('is_issueable');
		$transaction->addCondition('is_issueable',true);
		if($_GET['item'])
			$transaction->addCondition('item_id',$this->item);
	
		$transaction->addCondition('transaction_type',array('Issue','Submit','DeadSubmit','UsedSubmit'));
		 
		$grid=$this->add('Grid_AccountsBase');
		$member_model=$this->add('Model_Stock_Member');
		if($this->filter){			
			
			if($this->member){
				// throw $this->exception("Member_id".$this->member.$_GET['type']);
				$member_model->load($this->member);
				$transaction->addCondition('member_id',$this->member);
				$openning_bal=$member_model->getOpeningQty($this->member,$this->item,$this->from_date);
			}
			if($this->from_date)
				$transaction->addCondition('created_at','>=',$this->from_date);
			if($this->to_date)
				$transaction->addCondition('created_at','<=',$this->to_date);

		}else{
			$transaction->addCondition('id',-1);
		}
		
		$grid->setModel($transaction,array('item','qty','created_at','transaction_type'));	

		$grid->addColumn('text','DR');
		$grid->addColumn('text','CR');
		$grid->removeColumn('qty');
		
		$grid->addSno();

		$grid->addHook('formatRow',function($g){
			if(in_array($g->model['transaction_type'],array('Purchase','Submit','Transfer','Openning','DeadSubmit','UsedSubmit'))){
				$fill='DR';
				$no_fill='CR';
			}else{
				$fill='CR';
				$no_fill='DR';
			}

			if($g->model['transaction_type']=='Transfer' and $grid->model['to_branch_id']!=$grid->api->currentBranch->id){
				$fill='CR';
				$no_fill='DR';
			}
				$g->current_row[$fill] = $g->model['qty'];
				$g->current_row[$no_fill] ='';
		});


		
		if($this->item){
			$grid->addOpeningBalance($openning_bal,'CR',array('item'=>'Openning Balance'),'CR');
			$grid->addCurrentBalanceInEachRow('Balance','last','CR','CR','DR');
		}
		else{
			$grid->addMethod('format_balance',function($grid,$field){
				$m_temp = $grid->add('Model_Stock_Member');
				$grid->current_row[$field]= $m_temp->getOpeningQty($grid->model['member_id'],$grid->model['item_id'],$grid->api->nextDate($grid->model['created_at']));
			});
			$grid->addColumn('balance','balance');
		}

	}
}