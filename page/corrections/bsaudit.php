<?php


class page_corrections_bsaudit extends Page {
	
	public $title = "Audit Page";

	function page_index(){
		// parent::init();

		// if(!$this->app->auth->model->isSuper()){
		// 	$this->add('View_Error')->set('Not permitted');
		// 	return;
		// }

		$tabs = $this->add('Tabs');
		$trans_tab = $tabs->addTabURL($this->app->url('./tran'),'Transactions CR != DR');
		$trans_tab = $tabs->addTabURL($this->app->url('./trrow'),'Row Not Having Transaction');
	}

	function page_tran(){
		$model = $this->add('Model_Transaction');
		$model->addCondition('created_at','>','2018-04-01');
		$model->addCondition('created_at','<','2019-04-01');
		$model->addCondition('cr_sum','<>',$model->getElement('dr_sum'));

		$grid = $this->add('Grid');
		$grid->setModel($model);
		$grid->addPaginator(100);
	}

	function page_trrow(){
		$model = $this->add('Model_TransactionRow');
		$model->addExpression('has_transaction')->set(function($m,$q){
			return $m->add('Model_Transaction')->addCondition('id',$m->getElement('transaction_id'))->count();
		});
		$model->addCondition('created_at','>','2018-04-01');
		$model->addCondition('created_at','<','2019-04-01');
		$model->addCondition([['has_transaction',0],['has_transaction',null]]);

		$grid = $this->add('Grid');
		$grid->setModel($model);
		$grid->addPaginator(100);
	}

}