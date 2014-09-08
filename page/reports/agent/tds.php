<?php


class page_reports_agent_tds extends Page {
	public $title='Agent TDS Report';


	function init(){
		parent::init();

		$form = $this->add('Form');
		$agent_field=$form->addField('autocomplete/Basic','agent');
		$agent_field->setModel('Agent');
		$form->addField('DatePicker','from_date');
		$form->addField('DatePicker','to_date');
		$form->addField('DropDown','account_type')->setValueList(array('%'=>'All','DDS'=>'DDS','Recurring'=>'Recurring','FD'=>'FD','MIS'=>'MIS'));

		$form->addSubmit('Go');

		$grid = $this->add('Grid');

		$model = $this->add('Model_Agent');
		$account_join_2 = $model->join('accounts.agent_id');
		$transactions_join_2 = $account_join_2->join('transactions.reference_account_id');
		
		$account_join_2->addField('account_type');
		$transactions_join_2->addField('transaction_date','created_at');


		$tr_m = $model->add('Model_TransactionRow',array('table_alias'=>'xtc'));
		$tr = $tr_m->join('transactions','transaction_id');
		$releted_account = $tr->join('accounts','reference_account_id');
		$releted_account->addField('account_type');



		$model->addExpression('total_commission')->set(function($m,$q){
			
			$tr_m = $m->add('Model_TransactionRow',array('table_alias'=>'tc'));
			$tr = $tr_m->join('transactions','transaction_id');

			$releted_account = $tr->join('accounts','reference_account_id');
			$releted_account->addField('agent_id');
			$releted_account->addField('account_type');

			$tr_m->addCondition('agent_id',$q->getField('id'));
			$debited_account = $tr_m->join('accounts','account_id');
			$debited_account->addField('AccountNumber');

			$tr_m->addCondition('AccountNumber','like','%Commission Paid On%');

			if($_GET['account_type'])
				$tr_m->addCondition('account_type','like',$_GET['account_type']);

			if($_GET['from_date'])
				$tr_m->addCondition('created_at','>=',date('Y-m-01',strtotime($_GET['from_date'])));

			if($_GET['to_date'])
				$tr_m->addCondition('created_at','<=',date('Y-m-t',strtotime($_GET['to_date'])));

			return $tr_m->sum('amountDr');

			/*
				SUM(amountDr) 
				where
				Trnsaction_Row -> transaction whose releted_account_id's agent is me
				row accountnumber like Commission paid on ....
				created_at between filter values 01-m-Y of from_date --to-- t-m-y of to_date
			*/
		});

		// $model->addExpression('total_tds')->set(function($m,$q){
			/*
				SUM(amountCr) 
				where
				Trnsaction_Row -> transaction whose releted_account_id's agent is me
				row account number like TDS (under scheme duties and taxes ???)
				created_at between filter values 01-m-Y of from_date --to-- t-m-y of to_date
			*/
		// });

		$model->addExpression('net_commission')->set(function($m,$q){
			/*
				SUM(amountCr) 
				where
				Trnsaction_Row -> transaction whose releted_account_id's agent is me
				transaction_row account_id is my account_id
				created_at between filter values 01-m-Y of from_date --to-- t-m-y of to_date
			*/
		
			$tr_m = $m->add('Model_TransactionRow',array('table_alias'=>'tc'));
			$tr = $tr_m->join('transactions','transaction_id');

			$releted_account = $tr->join('accounts','reference_account_id');
			$releted_account->addField('agent_id');
			$releted_account->addField('account_type');

			$tr_m->addCondition('agent_id',$q->getField('id'));
			$tr_m->addCondition('account_id',$q->getField('account_id'));

			if($_GET['account_type'])
				$tr_m->addCondition('account_type','like',$_GET['account_type']);

			if($_GET['from_date'])
				$tr_m->addCondition('created_at','>=',date('Y-m-01',strtotime($_GET['from_date'])));

			if($_GET['to_date'])
				$tr_m->addCondition('created_at','<=',date('Y-m-t',strtotime($m->api->nextDate($_GET['to_date']))));

			return $tr_m->sum('amountCr');
		});


		if($_GET['filter']){
			$this->api->stickyGET("filter");
			$this->api->stickyGET("from_date");
			$this->api->stickyGET("to_date");
			$this->api->stickyGET("account_type");

			if($_GET['account_type']){
				
				$model->addCondition('account_type','like',$_GET['account_type']);
			}

			if($_GET['from_date'])
				$model->addCondition('transaction_date','>=',date('Y-m-01',strtotime($_GET['from_date'])));

			if($_GET['to_date'])
				$model->addCondition('transaction_date','<=',date('Y-m-t',strtotime($_GET['to_date'])));
			if($_GET['agent'])
				$model->addCondition('agent_id',$_GET['agent']);
		}else
			$model->addCondition('id',-1);

		$model->_dsql()->having('total_commission','>',0);
		$grid->setModel($model);

		$grid->add('View',null,'grid_buttons')->set('From ' . date('01-m-Y',strtotime($_GET['from_date'])). ' to ' . date('t-m-Y',strtotime($_GET['to_date'])) );
		$grid->addPaginator(10);

		if($form->isSubmitted()){
			$grid->js()->reload(array(
					'filter'=>1,
					'from_date'=>$form['from_date']?:0,
					'to_date'=>$form['to_date']?:0,
					'account_type'=>$form['account_type']
				))->execute();
		}


			
	}

	// function init(){
	// 	parent::init();

	// 	$form = $this->add('Form');
	// 	$form->addField('DatePicker','from_date');
	// 	$form->addField('DatePicker','to_date');
	// 	$form->addField('DropDown','account_type')->setValueList(array('%'=>'All','DDS'=>'DDS','Recurring'=>'Recurring','FD'=>'FD','MIS'=>'MIS'));
		
	// 	$grid = $this->add('Grid_AccountsBase');

	// 	$model=$this->add('Model_TransactionRow');
	// 	$agent_join = $model->join('agents.account_id','account_id');
	// 	$agent_member_join = $agent_join->join('members','member_id');
	// 	$transaction_type_join = $model->transaction_join->join('transaction_types','transaction_type_id');

	// 	$model->_dsql()->del('fields')->field($model->dsql()->expr('sum(amountCr) sum_amount'));
	// 	$model->_dsql()->field($model->dsql()->expr('MONTH('.$model->transaction_join->table_alias.'.created_at) month'));
	// 	$model->_dsql()->field($model->dsql()->expr($transaction_type_join->table_alias.'.name transaction_type'));
	// 	$model->_dsql()->field($model->dsql()->expr($agent_member_join->table_alias.'.name agent_name'));
	// 	$model->_dsql()->field($model->dsql()->expr($agent_member_join->table_alias.'.PanNo PanNo'));
			
	// 	$total_commission_model = $this->add('Model_TransactionRow',array('table_alias'=>'total_commission_table'));
	// 	$total_commission_model_account_join = $total_commission_model->join('accounts','account_id');
	// 	$total_commission_model_account_join->addField('AccountNumber');
	// 	$total_commission_model->addCOndition('AccountNumber','like','%Commission Paid On%');


	// 	$total_commission_model->_dsql()->del('fields')->field('1234');

	// 	// $model->_dsql()->field('total_commission');
	// 	if($_GET['filter']){

	// 	}

	// 	$model->_dsql()->field($model->dsql()->expr('('.$total_commission_model->_dsql()->render().') total_commission'));



	// 	$model->_dsql()->having(
 //            $model->dsql()->orExpr()
 //                ->where('transaction_type', TRA_ACCOUNT_OPEN_AGENT_COMMISSION)
 //                ->where('transaction_type', TRA_PREMIUM_AGENT_COMMISSION_DEPOSIT)
 //        );

	// 	$model->_dsql()->group('transaction_row.account_id,month');

	// 	$grid->setSource($model->debug()->_dsql());
	// 	$grid->addColumn('agent_name');
	// 	$grid->addColumn('sum_amount');
	// 	$grid->addColumn('month');
	// 	$grid->addColumn('PanNo');
	// 	$grid->addColumn('total_commission');
	// 	$grid->addColumn('transaction_type');

	// 	$grid->addPaginator(50);

	// 	if($form->isSubmitted()){
	// 		$grid->js()->reload(array(
	// 				'filter'=>1,
	// 				'from_date'=>$_GET['from_date']?:0,
	// 				'to_date'=>$_GET['to_date']?:0,
	// 				'account_type'=>$_GET['account_type']
	// 			))->execute();
	// 	}

	// }
}