<?php

class page_reports_deposit_tdsquaterly extends Page {
	public $title="TDS Quaterly Reports";
	function page_index(){
		// parent::init();

		$form=$this->add('Form');
		$dealer=$form->addField('DropDown','qtr')->setValueList(array('1'=>'1 Quarter','2'=>'2 Quarter','3'=>'3 Quarter','4'=>'4 Quarter'));

		$form->addSubmit('GET List');

		$grid = $this->add('Grid_AccountsBase');
		$grid->add('H3',null,'grid_buttons')->set('TDS Report As On ');

		$model = $this->add('Model_Transaction');
		$model->addCondition('transaction_type',array(TRA_ACCOUNT_OPEN_AGENT_COMMISSION,TRA_PREMIUM_AGENT_COMMISSION_DEPOSIT,TRA_PREMIUM_AGENT_COLLECTION_CHARGE_DEPOSIT));

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

		$model->getElement('reference_id')->caption('Account');
		$model->getElement('dr_sum')->caption('Net Amount');
		$model->getElement('agent_id')->caption('Name and Address');


		if($_GET['filter']){
			$year = date('Y',strtotime($this->api->today));
			$from_date= date('Y-m-d',strtotime("2015-01-01"));
			$to_date= date('Y-m-d',strtotime("2015-03-31"));

			$model->addCondition('created_at','>=',date('Y-m-01',strtotime($from_date)));
			$model->addCondition('created_at','<=',date('Y-m-t',strtotime($to_date)));

		}else
			$model->addCondition('id',-1);
		
		$model->setLimit(10);
		$model->_dsql()->group('agent_id');
		$grid->setModel($model,array('agent_id','agent','PanNo','tds_per','total_comm','tds_amt','dr_sum'));
		
		$grid->addColumn('month');
		$grid->addColumn('date_of_tds');
		$grid->addColumn('ch_no');

		$grid->addMethod('format_agent_id',function($g,$f){
				if($g->model['agent_id']){
					$agent_m = $g->add('Model_Agent')->tryLoad($g->model['agent_id']);				
					$g->current_row[$f] = $agent_m['name'];
				}
				
			});
		$grid->addFormatter('agent_id','agent_id,Wrap');

		$grid->add('View',null,'grid_buttons')->set('From ' . date('01-m-Y',strtotime($_GET['from_date'])). ' to ' . date('t-m-Y',strtotime($_GET['to_date'])) );
		$grid->addPaginator(50);
		$grid->addSno();

		if($form->isSubmitted()){
			$grid->js()->reload(array(
					'filter'=>1,
					'qtr'=>$form['qtr'],
				))->execute();
		}
	}

}

		// $grid->addColumn('net_amount');
