<?php

class page_memorandum_statement extends Page{
	public $title = "Memorandum Account Statement";

	function init(){
		parent::init();

		$account_id = $this->app->stickyGET('account_id')?:-1;
		$form=$this->add('Form');
		$account_field = $form->addField('autocomplete/Basic','account')->validateNotNull();
		$account_field->setModel('Account');
		$form->addSubmit('Get Statement');

		$v = $this->add('View')->addStyle('width','100%');

		if($form->isSubmitted()){	
			$v->js()->reload(
					array(
						'account_id'=>$form['account']
						// 'from_date'=>($form['from_date'])?:0,
						// 'to_date'=>($form['to_date'])?:0,
						)
					)->execute();
		}

		$grid = $v->add('Grid_AccountStatement');
		// $grid->addOpeningBalance(0,"amount_cr",array('Narration'=>"OP Narration"),'CR');
		$grid->addCurrentBalanceInEachRow();

		$memo_tra_row_model = $this->add('Model_Memorandum_TransactionRow');
		$memo_tra_row_model->addExpression('transaction')->set(function($m,$q){
			return $q->expr('CONCAT([0]," ", [1])',[$m->refSQL('memorandum_transaction_id')->fieldQuery('memorandum_type'),$m->refSQL('memorandum_transaction_id')->fieldQuery('name')]);
		});
		$memo_tra_row_model->addExpression('narration')->set(function($m,$q){
			return $q->expr('[0]',[$m->refSQL('memorandum_transaction_id')->fieldQuery('narration')]);
		});

		$memo_tra_row_model->addCondition('account_id',$account_id);
		$memo_tra_row_model->setOrder('created_at');
		// $grid->add('View',null,'grid_buttons')->setHtml('<div style="text-align:center;font-size:20px">'.$title_acc_name.' <br><small>'. $joint_memebrs .'</small> <br/> <small >From Date - '.$t_from_date." - " . "   To Date - ".($_GET['to_date']?:$this->api->today."</small></div>"));
		$grid->setModel($memo_tra_row_model,['transaction','account','created_at','narration','tax','tax_amount','tax_narration','amountCr','amountDr']);
		$grid->addFormatter('narration','Wrap');
		$grid->addFormatter('account','Wrap');
		$grid->addFormatter('transaction','Wrap');
	}
}