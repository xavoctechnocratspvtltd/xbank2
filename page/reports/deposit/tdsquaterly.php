<?php

class page_reports_deposit_tdsquaterly extends Page {
	public $title="TDS Quaterly Reports";
	function page_index(){
		// parent::init();
		
		$form=$this->add('Form');
		$dealer=$form->addField('DropDown','qtr')->setValueList(array('04'=>'1 Quarter','07'=>'2 Quarter','10'=>'3 Quarter','01'=>'4 Quarter'));

		$form->addSubmit('GET List');

		$grid = $this->add('Grid_Report_TdsQuaterly');

		$agent_model = $this->add('Model_Agent');

		$from_date = '-';
		$to_date = '-';

		if($_GET['filter']){
			$this->api->stickyGET('filter');
			$date = $this->api->today;
			if($_GET['qtr']){
				$this->api->stickyGET('qtr');
				$year = date('Y',strtotime($date));
				if($_GET['qtr']=='01') $year++;
				$date = $year.'-'.$_GET['qtr'].'-'.'01';
			}

			
			$quarter_date = $this->api->getFinancialQuarter($date);
			$from_date = $quarter_date['start_date'];
			$to_date = $quarter_date['end_date'];

			$grid->add('H3',null,'grid_buttons')->set('TDS Report From: '.$from_date.' To Date '.$to_date);
			// throw new \Exception($date.'::'.$to_date."::".$from_date);

		}else
			$agent_model->addCondition('id',-1);


		$agent_model->addExpression('total_commission')->set(function($m,$q)use($from_date, $to_date){
			$tr_row = $m->add('Model_TransactionRow',array('table_alias'=>'tcomm'));
			$tr_j = $tr_row->join('transactions','transaction_id');
			$tr_j->join('transaction_types','transaction_type_id')
				->addField('transaction_type_name','name');
			$tr_j->addField('tr_created_at','created_at');
			
			$account_j = $tr_row->join('transactions','transaction_id')
				->join('accounts','reference_id');
			
			$account_j->addField('agent_id');

			$agent_j = $account_j
				->join('agents','agent_id')
				;

			$tr_row->addCondition('agent_id',$q->getField('id'));
			$tr_row->addCondition('tr_created_at','>=',$from_date);
			$tr_row->addCondition('tr_created_at','<',$m->api->nextDate($to_date));
			$tr_row->addCondition('transaction_type_name',array(TRA_ACCOUNT_OPEN_AGENT_COMMISSION,TRA_PREMIUM_AGENT_COMMISSION_DEPOSIT,TRA_PREMIUM_AGENT_COLLECTION_CHARGE_DEPOSIT));
			// $tr_row->addCondition('branch_id',2);

			return $tr_row->sum('amountDr');

		});

		$agent_model->addExpression('total_tds')->set(function($m,$q)use($from_date, $to_date){
			$tr_row = $m->add('Model_TransactionRow',array('table_alias'=>'ttds'));
			$tr_j = $tr_row->join('transactions','transaction_id');
			$tr_j->join('transaction_types','transaction_type_id')
				->addField('transaction_type_name','name');
			
			$account_j = $tr_row->join('transactions','transaction_id')
				->join('accounts','reference_id');
			
			$account_j->addField('agent_id');

			$agent_j = $account_j
				->join('agents','agent_id')
				;

			$tr_row->addCondition('agent_id',$q->getField('id'));
			$tr_row->addCondition('created_at','>=',$from_date);
			$tr_row->addCondition('created_at','<',$m->api->nextDate($to_date));
			$tr_row->addCondition('transaction_type_name',array(TRA_ACCOUNT_OPEN_AGENT_COMMISSION,TRA_PREMIUM_AGENT_COMMISSION_DEPOSIT,TRA_PREMIUM_AGENT_COLLECTION_CHARGE_DEPOSIT));
			$tr_row->addCondition('account','like','%TDS%');
			// $tr_row->addCondition('branch_id',2);

			return $tr_row->sum('amountCr');

		});
		
		$agent_model->addExpression('net_commission')->set(function($m,$q)use($from_date, $to_date){
			$tr_row = $m->add('Model_TransactionRow',array('table_alias'=>'ncomm'));
			$tr_j = $tr_row->join('transactions','transaction_id');
			$tr_j->join('transaction_types','transaction_type_id')
				->addField('transaction_type_name','name');
			
			$account_j = $tr_row->join('transactions','transaction_id')
				->join('accounts','reference_id');
			
			$account_j->addField('agent_id');

			$agent_j = $account_j
				->join('agents','agent_id')
				;

			$tr_row->addCondition('agent_id',$q->getField('id'));
			$tr_row->addCondition('created_at','>=',$from_date);
			$tr_row->addCondition('created_at','<',$m->api->nextDate($to_date));
			$tr_row->addCondition('transaction_type_name',array(TRA_ACCOUNT_OPEN_AGENT_COMMISSION,TRA_PREMIUM_AGENT_COMMISSION_DEPOSIT,TRA_PREMIUM_AGENT_COLLECTION_CHARGE_DEPOSIT));
			$tr_row->addCondition('account','not like','%TDS%');
			// $tr_row->addCondition('branch_id',2);

			return $tr_row->sum('amountCr');

		});
		

		

		$grid->setModel($agent_model->debug(),array('name','total_commission','total_tds','net_commission'));
		
		$grid->addColumn('ch_no');
		
		if($form->isSubmitted()){
			$grid->js()->reload(array(
					'filter'=>1,
					'qtr'=>$form['qtr'],
				))->execute();
		}
	}

}

		// $grid->addColumn('net_amount');
