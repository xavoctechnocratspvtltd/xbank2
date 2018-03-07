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
				$account_types=['RD'=>TRA_RECURRING_ACCOUNT_AMOUNT_DEPOSIT,'DDS'=>TRA_DDS_ACCOUNT_AMOUNT_DEPOSIT];
				$model = $this->add('Model_Agent');
				$fields=['name'];
				$remove_zero_condition=[];
				foreach ($account_types as $key=>$tr_type) {
					$model->addExpression($key.'_amount_compared')->set(function($m,$q)use($fd,$td,$tr_type){
						$tr = $m->add('Model_TransactionRow');
						$tr->addCondition('transaction_type',$tr_type);
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

					})->sortable(true);

					$model->addExpression($key.'_accounts_count')->set(function($m,$q)use($fd,$td,$tr_type){
						$tr = $m->add('Model_TransactionRow',['table_alias'=>'xyz']);
						$tr->addCondition('transaction_type',$tr_type);
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
						return $tr->_dsql()->del('fields')->field($q->expr('count(DISTINCT([0]))',[$tr->getElement('reference_id')]));
					})->sortable(true);

					$fields[] = $key.'_accounts_count';
					$fields[] = $key.'_amount_compared';
					$remove_zero_condition[] = [$key.'_accounts_count','>',0];
				}

				if($br){					
					$model->addCondition('branch_id',$br);
				}

				$model->getElement('name')->sortable(true);
				$model->addCondition($remove_zero_condition);
				$model->setOrder('RD_amount_compared','desc');
				// $model->setOrder('amount_compared','desc');
				$model->_dsql()->group('id');
				$grid = $view->add('Grid');
				$grid->setModel($model,$fields);
				$grid->addPaginator(50);

			}elseif($rt == 'account'){
				$account_types=['DDS','FixedAndMis','Recurring'];
				$model = $this->add('Model_Agent');
				$fields=['name'];
				$remove_zero_condition=[];
				foreach ($account_types as $acc_type) {
					$model->addExpression($acc_type.'_amount_compared')->set(function($m,$q)use($fd,$td,$acc_type){
						$acc = $m->add('Model_Account');
						$acc->addCondition('SchemeType',$acc_type);
						$acc->addCondition('agent_id',$q->getField('id'));

						if($fd){
							$acc->addCondition('created_at','>=',$fd);
						}
						if($td){
							$acc->addCondition('created_at','<',$this->app->nextDate($td));
						}
						return $acc->_dsql()->del('fields')->field('sum(Amount)');

					})->sortable(true);

					$model->addExpression($acc_type.'_accounts_count')->set(function($m,$q)use($fd,$td,$acc_type){
						$acc = $m->add('Model_Account');
						$acc->addCondition('SchemeType',$acc_type);
						$acc->addCondition('agent_id',$q->getField('id'));

						if($fd){
							$acc->addCondition('created_at','>=',$fd);
						}
						if($td){
							$acc->addCondition('created_at','<',$this->app->nextDate($td));
						}
						return $acc->_dsql()->del('fields')->field('count(Amount)');
					})->sortable(true);

					$fields[] = $acc_type.'_accounts_count';
					$fields[] = $acc_type.'_amount_compared';
					$remove_zero_condition[] = [$acc_type.'_accounts_count','>',0];
				}

				if($br){					
					$model->addCondition('branch_id',$br);
				}

				$model->getElement('name')->sortable(true);
				$model->addCondition($remove_zero_condition);
				// $model->setOrder('amount_compared','desc');
				$model->_dsql()->group('id');
				$grid = $view->add('Grid');
				$grid->setModel($model,$fields);
				$grid->addPaginator(50);

			}

			
		}


		if($form->isSubmitted()){
			$view->js()->reload(array('report_type'=>$form['report_type'],'branch'=>$form['branch'], 'to_date'=>$form['to_date']?:'0','from_date'=>$form['from_date']?:'0','filter'=>1))->execute();
		}
	}
}