<?php

class page_reports_transactioncount extends Page {
	public $title ="Transaction Count Reports";
	function init(){
		parent::init();

		$form = $this->add('Form')->addClass('noneprintalbe');
		$form->addField('DatePicker','from_date')->validateNotNull();
		$form->addField('DatePicker','to_date')->validateNotNull();
		$form->addField('DropDown','voucher_type')->setEmptyText('All')->setModel('TransactionType');

		if($this->app->auth->model->isSuper()){
			$form->addField('DropDown','branch')->setEmptyText('All')->setModel('Branch');
		}

		$form->addSubmit('Go');

		$transaction_model = $this->add('Model_Transaction');
		$transaction_model->addExpression('count','count(*)');
		$transaction_model->add('Controller_Acl');
		$transaction_model->addCondition([['transaction_type_id','<>',false],['transaction_type_id','is not',null]]);

		if($_GET['from_date']){
			$transaction_model->addCondition('created_at','>=',$_GET['from_date']);
		}

		if($_GET['to_date']){
			$transaction_model->addCondition('created_at','<',$this->app->nextDate($_GET['to_date']));
		}

		if($_GET['branch']){
			$transaction_model->addCondition('branch_id',$_GET['branch']);
		}

		if($_GET['voucher_type']){
			$transaction_model->addCondition('transaction_type_id',$_GET['voucher_type']);
		}else{
			$transaction_model->_dsql()->group('transaction_type_id');
		}

		$grid = $this->add('Grid');
		$grid->setModel($transaction_model,['transaction_type','count']);

		if($form->isSubmitted()){
			$grid->js()->reload(array('to_date'=>$form['to_date']?:0,'from_date'=>$form['from_date']?:0,'voucher_type'=>$form['voucher_type'],'branch'=>$form['branch']?:0))->execute();
		}


	}
}