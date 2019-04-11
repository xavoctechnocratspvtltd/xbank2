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
		$memo_tra_row_model->addCondition('account_id',$account_id);
		$memo_tra_row_model->setOrder('created_at');
		// $grid->add('View',null,'grid_buttons')->setHtml('<div style="text-align:center;font-size:20px">'.$title_acc_name.' <br><small>'. $joint_memebrs .'</small> <br/> <small >From Date - '.$t_from_date." - " . "   To Date - ".($_GET['to_date']?:$this->api->today."</small></div>"));
		$grid->setModel($memo_tra_row_model,['memorandum_transaction','created_at','tax','tax_amount','tax_narration','amountCr','amountDr']);
	}
}