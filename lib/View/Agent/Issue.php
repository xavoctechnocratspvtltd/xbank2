<?php

class View_Agent_Issue extends View {
	function init(){
		parent::init();
		// if(!$_GET['filter'])
		// 	throw $this->exception('Something is wrong');
		// 	
		$this->add('H3')->set('Agent Issue Ledger');
		$this->api->stickyGET('agent');
		$this->api->stickyGET('from_date');
		$this->api->stickyGET('to_date');

		$transaction=$this->add('Model_Stock_Transaction');
		$transaction_j_item=$transaction->join('stock_items','item_id');
		$transaction_j_item->addField('is_issueable');
		$transaction->addCondition('is_issueable',true);
		$transaction->addCondition('transaction_type',array('Issue','Submit'));

		$grid=$this->add('Grid_AccountsBase');
		$agent_model=$this->add('Model_Stock_Agent');
		if($_GET['filter']){
						
			if($_GET['agent']){
				$agent_model->load($_GET['agent']);
				$transaction->addCondition('member_id',$_GET['agent']);
			}

			if($_GET['from_date'])
				$transaction->addCondition('created_at','>=',$_GET['from_date']);
			if($_GET['to_date'])
				$transaction->addCondition('created_at','<=',$_GET['to_date']);

		}else
			$transaction->addCondition('id',-1);

		// $openning_bal=$agent_model->getQty($_GET['from_date']);
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