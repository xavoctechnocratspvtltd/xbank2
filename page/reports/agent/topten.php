<?php

class page_reports_agent_topten extends Page {
	public $title="Top 10";
	
	function init(){
		parent::init();
		
		$form=$this->add('Form',null,null,['form/horizontal']);
		$form->addField('DatePicker','from_date')->validateNotNull();
		$form->addField('DatePicker','to_date')->validateNotNull();
		$form->addField('Dropdown','report_type')->validateNotNull()->setEmptyText("Please Select")->setValueList(['collection'=>"Collection Wise",'account'=>'Account Wise']);
		$form->addField('Dropdown','branch')->setEmptyText("All")->setModel('Branch');
		$form->addField('autocomplete/Basic','scheme')->setModel('Scheme');
		$form->addField('Number','duration');
		$form->addField('Dropdown','duration_unit')->setEmptyText('unit')->setValueList(['Month'=>'Month','Day'=>'Day']);

		$form->addSubmit('GET LIST');

		$rt = $this->app->stickyGET('report_type');
		$br = $this->app->stickyGET('branch');
		$fd = $this->app->stickyGET('from_date');
		$td = $this->app->stickyGET('to_date');
		$fl = $this->app->stickyGET('filter');
		$scheme_id = $this->app->stickyGET('scheme');
		$duration = $this->app->stickyGET('duration');
		$duration_unit = $this->app->stickyGET('duration_unit');

		$view = $this->add('View');
		if($duration_unit == "Day")
			$view->add('View')->setElement('h3')->set('Show Records of Fixed and MIS Account');
		elseif($duration_unit == "Month")
			$view->add('View')->setElement('h3')->set('Show Records of DDD, RD, Loan');
				
		if($fl){
			$model = $this->add('Model_Agent');
			$fields = ['name'];

			if($scheme_id OR $duration){
				$fields = ['name','scheme_id','MaturityPeriod','SchemeType'];

				$agent_acct_join = $model->join('accounts','account_id');
				$agent_acct_join->addField('scheme_id');
				$scheme_join = $agent_acct_join->join('schemes','scheme_id');
				$scheme_join->addField('SchemeType');
				$scheme_join->addField('MaturityPeriod');
			
				if($scheme_id){
					$model->addCondition('scheme_id',$scheme_id);
				}elseif($duration){
					if($duration_unit == "Day"){
						$model->addCondition('SchemeType',ACCOUNT_TYPE_FIXED);
					}
					if($duration_unit == "Month"){
						$model->addCondition('SchemeType',['DDS','Loan','Recurring']);
					}
					$model->addCondition('MaturityPeriod','>=',$duration);
				}
			}

			/* month: 
				$this->addCondition('SchemeType','DDS');
				$this->addCondition('SchemeType','Loan');
				$this->addCondition('SchemeType','Recurring');
				form submission report_type must be Collection_wise
				Days
					$this->addCondition('SchemeType',ACCOUNT_TYPE_FIXED);
				form submission report_type must be Account_wise
			*/



			if($rt =='collection'){
				$account_types=['RD'=>TRA_RECURRING_ACCOUNT_AMOUNT_DEPOSIT,'DDS'=>TRA_DDS_ACCOUNT_AMOUNT_DEPOSIT];
				if($duration_unit == "Day"){
					$account_types = [];
				}

				// $fields=['name'];
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
				if($duration_unit == "Month"){
					$account_types = ['FixedAndMis'];
				}
				// $model = $this->add('Model_Agent');
				// $fields=['name'];
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
			if($form['duration'] AND !$form['duration_unit']) $form->displayError('duration_unit','must not be empty');
			if(!$form['duration'] AND $form['duration_unit']) $form->displayError('duration','must not be empty');

			if($form['duration_unit'] == "Day" && $form['report_type'] == "collection") $form->displayError('report_type','must be account wise');

			$view->js()->reload(array('report_type'=>$form['report_type'],'branch'=>$form['branch'], 'to_date'=>$form['to_date']?:'0','from_date'=>$form['from_date']?:'0','scheme'=>$form['scheme'],'duration'=>$form['duration'],'duration_unit'=>$form['duration_unit'],'filter'=>1))->execute();
		}
	}
}