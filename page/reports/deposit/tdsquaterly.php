<?php

class page_reports_deposit_tdsquaterly extends Page {
	public $title="TDS Quaterly Reports";
	function page_index(){
		// parent::init();
		
		$form=$this->add('Form');
		$dealer=$form->addField('DropDown','qtr')->setValueList(array('01'=>'1 Quarter','04'=>'2 Quarter','07'=>'3 Quarter','10'=>'4 Quarter'));

		$form->addSubmit('GET List');

		$grid = $this->add('Grid_Report_TdsQuaterly');

		$model = $this->add('Model_Transaction');
		$model->addCondition('transaction_type',array(TRA_ACCOUNT_OPEN_AGENT_COMMISSION,TRA_PREMIUM_AGENT_COMMISSION_DEPOSIT,TRA_PREMIUM_AGENT_COLLECTION_CHARGE_DEPOSIT));
		$model->addCondition('branch_id',$this->api->current_branch->id);

		$reference_account_j = $model->join('accounts','reference_id');
		$reference_account_j->addField('agent_id');
		$reference_account_j->addField('account_type');
		$agent_j = $reference_account_j->LeftJoin('agents','agent_id');
		$member_j = $agent_j->LeftJoin('members','member_id');
		$member_j->addField('PanNo');
		// $model->addExpression('total_commission')->set($model->fieldQuery('cr_sum'));
		$model->addExpression('tds_amt')->set($model->refSQL('TransactionRow')->addCondition('account','like','%TDS%')->sum('amountCr'));
		$model->addExpression('total_comm')->set($model->refSQL('TransactionRow')->addCondition('account','not like','%TDS%')->sum('amountCr'));
		$model->addExpression('tds_per')->set('10');
		$model->addExpression('month')->set('DATE_FORMAT('.$model->dsql()->getField('created_at').',"%M")');

		$model->getElement('reference_id')->caption('Account');
		$model->getElement('dr_sum')->caption('Net Amount');
		$model->getElement('agent_id')->caption('Name and Address');

		if($_GET['filter']){
			$this->api->stickyGET('filter');
			$date = $this->api->today;
			if($_GET['qtr']){
				$this->api->stickyGET('qtr');
				$year = date('Y',strtotime($date));
				$date = $year.'-'.$_GET['qtr'].'-'.'01';
			}

			
			$quarter_date = $this->api->getFinancialQuarter($date);
			$from_date = $quarter_date['start_date'];
			$to_date = $quarter_date['end_date'];

			$grid->add('H3',null,'grid_buttons')->set('TDS Report From: '.$from_date.' To Date'.$to_date);
			// throw new \Exception($date.'::'.$to_date."::".$from_date);
			$model->addCondition('created_at','>=',date('Y-m-01',strtotime($from_date)));
			$model->addCondition('created_at','<=',date('Y-m-d',strtotime($to_date)));

		}else
			$model->addCondition('id',-1);
		
		// $model->setLimit(10);
		$model->setOrder('created_at');
		$model->setOrder('agent_id');
		$model->_dsql()->group('agent_id');
		$model->_dsql()->group('month');

		$grid->setModel($model,array('agent_id','agent','PanNo','tds_per','total_comm','tds_amt','dr_sum','created_at','month'));
		
		// $grid->addColumn('month');
		// $grid->addColumn('date_of_tds');
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
