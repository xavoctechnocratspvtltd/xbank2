<?php

class page_stock_ledger_supplier extends Page {
	function init(){
		parent::init();

		$form=$this->add('Form');

		$supplier_field=$form->addField('dropdown','supplier')->setEmptyText('Please Select');
		$supplier_field->setModel('Stock_Party');

		$form->addField('DatePicker','from_date');
		$form->addField('DatePicker','to_date');
		$form->addSubmit('GET');

		$transaction=$this->add('Model_Stock_Transaction');

		$grid=$this->add('Grid_AccountsBase');
		if($_GET['filter']){
						 
			if($_GET['supplier']){	
				$transaction->addCondition('party_id',$_GET['supplier']);
			}

			if($_GET['from_date'])
				$transaction->addCondition('created_at','>=',$_GET['from_date']);
			if($_GET['to_date'])
				$transaction->addCondition('created_at','<=',$_GET['to_date']);

		}else
			$transaction->addCondition('id',-1);

		// $openning_bal=$staff_model->getQty($_GET['from_date']);
		$grid->setModel($transaction,array('item','qty','created_at'));	

		$grid->addColumn('text','DR');
		$grid->addColumn('text','CR');
		$grid->removeColumn('qty');
		
		$grid->addSno();

		$grid->addHook('formatRow',function($grid){
			if(in_array($grid->model['transaction_type'],array('Purchase','Submit','Transfer','Openning'))){
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



		if($form->isSubmitted()){
			$grid->js()->reload(array('supplier'=>$form['supplier'],'from_date'=>$form['from_date']?:0,'to_date'=>$form['to_date']?:0,'filter'=>1))->execute();
		}

	}
}