<?php

class page_reports_roperformance extends Page {
	public $title="RO Performance (MO With Loan Accounts)";

	function init(){
		parent::init();
		
		$from_date = $this->app->stickyGET('from_date');
		$to_date = $this->app->stickyGET('to_date');
		$mo_id = $this->app->stickyGET('mo_id');
		$filter = $this->app->stickyGET('filter');

		$vp = $this->add('VirtualPage');
		$vp->set([$this,'displayAgentAccoutns']);

		$form = $this->add('Form');
		$field_mo = $form->addField('autocomplete/Basic','mo','RO');
		$field_mo->setModel('Mo')->addCondition('is_active',1);
		$field_mo->validateNotNull();

		$form->addField('DatePicker','from_date')->validateNotNull();;
		$form->addField('DatePicker','to_date')->validateNotNull();
		$form->addSubmit('Filter');

		$model = $this->add('Model_MoAccountAssociation');

		if($filter){

			$model->addExpression('effective_from')->set(function($m,$q)use($from_date){
				return $q->expr('GREATEST([0],"[1]")',[$m->getElement('from_date'),$from_date]);
			})->type('datetime');

			$model->addExpression('effective_to')->set(function($m,$q)use($to_date){
				return $q->expr('LEAST([0],"[1]")',[$m->getElement('to_date'),$this->app->nextDate($to_date)]);
			})->type('datetime');

			// [TRA_LOAN_ACCOUNT_AMOUNT_DEPOSIT,TRA_PENALTY_AMOUNT_RECEIVED,TRA_OTHER_AMOUNT_RECEIVED]
			$model->addExpression('loan_amount_deposit')->set(function($m,$q)use($from_date, $to_date){
				$transaction_row_model = $m->add('Model_TransactionRow');
				$transaction_join = $transaction_row_model->join('transactions','transaction_id');
				$transaction_type_join = $transaction_join->join('transaction_types','transaction_type_id');
				$transaction_type_join->addField('transaction_type_name','name');
				$transaction_row_model->addCondition('transaction_type_name',TRA_LOAN_ACCOUNT_AMOUNT_DEPOSIT);
				$transaction_row_model
					->addCondition('account_id',$m->getElement('account_id'))
					->addCondition($q->expr('[0] >= GREATEST([1],"[2]")',[$transaction_row_model->getElement('created_at'),$m->getElement('from_date'),$from_date]))
					->addCondition($q->expr('[0] <= LEAST([1],"[2]")',[$transaction_row_model->getElement('created_at'),$m->getElement('to_date'),$to_date]))
					;
				return $transaction_row_model->sum('amountCr');
			})->type('money');

			$model->addExpression('penalty_amount_deposit')->set(function($m,$q)use($from_date, $to_date){
				$transaction_row_model = $m->add('Model_TransactionRow');
				$transaction_join = $transaction_row_model->join('transactions','transaction_id');
				$transaction_type_join = $transaction_join->join('transaction_types','transaction_type_id');
				$transaction_type_join->addField('transaction_type_name','name');
				$transaction_row_model->addCondition('transaction_type_name',TRA_PENALTY_AMOUNT_RECEIVED);
				$transaction_row_model
					->addCondition('account_id',$m->getElement('account_id'))
					->addCondition($q->expr('[0] >= GREATEST([1],"[2]")',[$transaction_row_model->getElement('created_at'),$m->getElement('from_date'),$from_date]))
					->addCondition($q->expr('[0] <= LEAST([1],"[2]")',[$transaction_row_model->getElement('created_at'),$m->getElement('to_date'),$to_date]))
					;
				return $transaction_row_model->sum('amountCr');
			})->type('money');

			$model->addExpression('other_amount_deposit')->set(function($m,$q)use($from_date, $to_date){
				$transaction_row_model = $m->add('Model_TransactionRow');
				$transaction_join = $transaction_row_model->join('transactions','transaction_id');
				$transaction_type_join = $transaction_join->join('transaction_types','transaction_type_id');
				$transaction_type_join->addField('transaction_type_name','name');
				$transaction_row_model->addCondition('transaction_type_name',TRA_OTHER_AMOUNT_RECEIVED);
				$transaction_row_model
					->addCondition('account_id',$m->getElement('account_id'))
					->addCondition($q->expr('[0] >= GREATEST([1],"[2]")',[$transaction_row_model->getElement('created_at'),$m->getElement('from_date'),$from_date]))
					->addCondition($q->expr('[0] <= LEAST([1],"[2]")',[$transaction_row_model->getElement('created_at'),$m->getElement('to_date'),$to_date]))
					;
				return $transaction_row_model->sum('amountCr');
			})->type('money');

			$model->addExpression('to_date_date')->set(function($m,$q){
				return $q->expr('Date([0])',[$m->getElement('to_date')]);
			});
			
			$model->addCondition('mo_id',$mo_id);
			$model->addCondition('from_date','<=',$to_date);
			$model->addCondition('to_date_date','>=',$from_date);

		}else{
			$model->addCondition('id','-1');
		}

		$documents=['BIKE SURRENDER','BIKE LOCATION','VISIT CHARGE','Recovery Status'];

		foreach ($documents as $dc) {
			$model->addExpression($this->app->normalizeName($dc))->set(function($m,$q)use($dc){
				return $this->add('Model_DocumentSubmitted')
							->addCondition('accounts_id',$q->getField('account_id'))
							->addCondition('documents',$dc)
							->fieldQuery('Description');
			});
		}

		$grid = $this->add('Grid_AccountsBase');
		$grid->setModel($model);
		$grid->addSno();
		$grid->addTotals(['loan_amount_deposit','penalty_amount_deposit','other_amount_deposit']);

		foreach ($documents as $dc) {
			$grid->addFormatter($this->app->normalizeName($dc),'wrap');
		}

		$grid->addHook('formatRow',function($g)use($documents){
			foreach ($documents as $dc) {
				$field=$this->app->normalizeName($dc);
				$g->current_row_html[$field] = '<div style="width:500px;">'.$g->current_row[$field].'</div>';
			}
			$field='account';
			$g->current_row_html[$field] = '<div style="width:500px;">'.$g->current_row[$field].'</div>';
		});

		//$grid->removeColumn('from_date');
		//$grid->removeColumn('to_date');
		$grid->removeColumn('_to_date');
		$grid->removeColumn('to_date_date');
		$grid->addFormatter('account','WRAP');

		if($filter){
			// $grid->setFormatter('crpb_sum','template')->setTemplate('<a class="crbp" href="#" data-agentid="{$agent_id}" data-agentfromdate="{$effective_from}" data-agenttodate="{$effective_to}" >{$crpb_sum}</a>','crpb_sum');
			$grid->js('click')->_selector('.crbp')->univ()->frameURL([
											$vp->getUrl(),
											'agent_id'=>$this->js()->_selectorThis()->data('agentid'),
											'agent_from_date'=>$this->js()->_selectorThis()->data('agentfromdate'),
											'agent_to_date'=>$this->js()->_selectorThis()->data('agenttodate'),
										]);
		}

		if($form->isSubmitted()){

			if(strtotime($form['from_date']) > strtotime($form['to_date']) ){
				$form->displayError('from_date','From Date must be smaller than To date');
			}

			$grid->js()->reload(array(
								'mo_id'=>$form['mo'],
								'from_date'=>$form['from_date'],
								'to_date'=>$form['to_date'],
								'filter'=>1
							))->execute();
		}

	}

	function displayAgentAccoutns($page){
		$this->app->stickyGET('from_date');
		$this->app->stickyGET('to_date');
		$this->app->stickyGET('agent_id');

		$model = $this->add('Model_Account');
		$model->addCondition('created_at','>=',$_GET['from_date']);
		$model->addCondition('created_at','<',$this->app->nextDate($_GET['to_date']));
		$model->addCondition('agent_id',$_GET['agent_id']);

		$grid = $page->add('Grid');
		$grid->setModel($model,['member','Amount','crpb','created_at']);
		$grid->addFormatter('member','WRAP');
		$grid->addPaginator(50);
		$grid->addTotals(['crpb']);
	}
}