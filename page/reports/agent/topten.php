<?php

class page_reports_agent_topten extends Page {
	public $title="Top 10";
	
	function init(){
		parent::init();
		
		$form=$this->add('Form');
		$form->addField('DatePicker','from_date')->validateNotNull();
		$form->addField('DatePicker','to_date')->validateNotNull();
		$form->addField('Dropdown','report_type')->validateNotNull()->setEmptyText("Please Select")->setValueList(['collection'=>"Collection Wise",'account'=>'Account Wise']);
		$form->addField('Dropdown','branch')->setEmptyText("All")->setModel('Branch');
		$form->addSubmit('GET LIST');

		$rt = $this->app->stickyGET('report_type');
		$br = $this->app->stickyGET('branch');
		$fd = $this->app->stickyGET('from_date');
		$td = $this->app->stickyGET('to_date');
		$fl = $this->app->stickyGET('filter');

		$view = $this->add('View');

		if($fl){
			if($rt =='collection'){
				$model = $this->add('Model_Agent');
				$acc_j = $model->join('accounts.agent_id');
				$model->addExpression('amount_compared')->set(function($m,$q)use($fd,$td){
					$tr = $m->add('Model_TransactionRow');
					$tr->addCondition('transaction_type',[TRA_RECURRING_ACCOUNT_AMOUNT_DEPOSIT,TRA_DDS_ACCOUNT_AMOUNT_DEPOSIT]);
					$tr_j = $tr->join('transactions','transaction_id');
					$ref_acc_j = $tr_j->join('accounts','reference_id');
					$ref_acc_j->addField('agent_id');
					$tr->addCondition('agent_id',$q->getField('id'));

					if($fd){
						$tr->addCondition('created_at','>=',$fd);
					}
					if($td){
						$tr->addCondition('created_at','<',$this->app->nextDate($td));
					}

					return $tr->_dsql()->del('fields')->field('sum(amountCr)');
				});

			}elseif($rt == 'account'){
				$model = $this->add('Model_Account');
				$field_sum='Amount';
			}

			$model->addCondition('amount_compared','>',0);
			$model->setOrder('amount_compared','desc');
			$model->_dsql()->group('agent_id');
			$grid = $view->add('Grid');
			$grid->setModel($model,['name','amount_compared']);
			$grid->addPaginator(50);
		}


		if($form->isSubmitted()){
			$view->js()->reload(array('report_type'=>$form['report_type'],'branch'=>$form['branch'], 'to_date'=>$form['to_date']?:'0','from_date'=>$form['from_date']?:'0','filter'=>1))->execute();
		}
	}
}