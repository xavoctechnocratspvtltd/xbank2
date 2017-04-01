<?php

class page_reports_deposit_tdsquaterly extends Page {
	public $title="TDS Quaterly Reports";

	function page_index(){

		$till_date = $this->api->today;
		if($_GET['to_date']) $till_date = $_GET['to_date'];

		$form = $this->add('Form');
		$agent_field=$form->addField('autocomplete/Basic','agent');
		$agent_field->setModel('Agent');
		$form->addField('DatePicker','from_date');
		$form->addField('DatePicker','to_date');
		// $form->addField('DropDown','account_type')->setValueList(array('%'=>'All','DDS'=>'DDS','Recurring'=>'Recurring','FD'=>'FD','MIS'=>'MIS'));

		$form->addSubmit('Go');

		$grid = $this->add('Grid_AccountsBase');
		$grid->add('H3',null,'grid_buttons')->set('Agent TDS Report As On '. date('d-M-Y',strtotime($till_date))); 

		$model = $this->add('Model_Transaction');
		$model->addCondition('transaction_type',array(TRA_ACCOUNT_OPEN_AGENT_COMMISSION,TRA_PREMIUM_AGENT_COMMISSION_DEPOSIT,TRA_PREMIUM_AGENT_COLLECTION_CHARGE_DEPOSIT));

		$reference_account_j = $model->join('accounts','reference_id');
		$agent_j=$reference_account_j->join('agents','agent_id');
		$reference_account_j->addField('agent_id');
		$reference_account_j->addField('account_type');
		$agent_member_j = $agent_j->join('members','member_id');
		$agent_member_j->addField('agent_name','name');
		$agent_member_j->addField('PanNo');
		$agent_member_j->addField('PermanentAddress');

		$agent_saving_account_j = $agent_j->join('accounts','account_id');
		$agent_saving_account_j->addField('saving_account','AccountNumber');

		// $model->addExpression('total_commission')->set($model->fieldQuery('cr_sum'));
		$model->addExpression('tds')->set($model->refSQL('TransactionRow')->addCondition('account','like','%TDS%')->sum('amountCr'));
		// $model->addExpression('tds')->set('"0"');
		$model->addExpression('tr_row_count')->set($model->refSQL('TransactionRow')->count());
		$model->addExpression('net_commission')->set($model->refSQL('TransactionRow')->addCondition('account','not like','%TDS%')->sum('amountCr'));

		$model->getElement('reference_id')->caption('Account');
		$model->getElement('dr_sum')->caption('Total Amount');

		$model->addExpression('sum_tds')->set($model->dsql()->expr('SUM([0])',array($model->getElement('tds'))));
		$model->addExpression('sum_net_commission')->set($model->dsql()->expr('SUM([0])',array($model->getElement('net_commission'))));
		$model->addExpression('sum_total')->set($model->dsql()->expr('SUM([0])',array($model->getElement('dr_sum'))));
		$model->addExpression('month')->set($model->dsql()->expr("DATE_FORMAT([0],'%m%Y')",array($model->getElement('created_at'))));
		$model->addExpression('month_display')->set($model->dsql()->expr("DATE_FORMAT([0],'%M %Y')",array($model->getElement('created_at'))));


		// $model->add('Controller_Acl');


		if($_GET['filter']){
			$this->api->stickyGET("filter");
			$this->api->stickyGET("from_date");
			$this->api->stickyGET("to_date");
			// $this->api->stickyGET("account_type");
			$this->api->stickyGET("agent");

			// if($_GET['account_type']){
			// 	$model->addCondition('account_type','like',$_GET['account_type']);
			// }

			if($_GET['from_date'])
				$model->addCondition('created_at','>=',$_GET['from_date']);

			if($_GET['to_date'])
				$model->addCondition('created_at','<',$this->api->nextDate($_GET['to_date']));

			if($_GET['agent'])
				$model->addCondition('agent_id',$_GET['agent']);
		}else
			$model->addCondition('id',-1);

		$model->addCondition('dr_sum','>',0);
		$model->addCondition('tr_row_count',3);

		$model->_dsql()->group($model->dsql()->expr('[1] asc,[0]',array($model->getElement('agent_name'),$model->getElement('month'))));
		$model->setOrder($model->dsql()->expr('[0],[1]',array($model->getElement('agent_name'),$model->getElement('month'))));

		// $model->setLimit(10);
		// $model->_dsql()->group('reference_id');
		$grid->setModel($model,array('agent_name','saving_account','PermanentAddress','PanNo','month','month_display','sum_total','sum_tds','sum_net_commission','created_at','PanNo'));
		
		if($_GET['agent'])
			$grid->removeColumn('agent_id');
		else{
			// $grid->addMethod('format_agent_id',function($g,$f){
			// 	if($g->model['agent_id']){
			// 		$agent_m = $g->add('Model_Agent')->tryLoad($g->model['agent_id']);				
			// 		$g->current_row[$f] = $agent_m['agent_member_name'];
			// 	}
				
			// });
			// $grid->addFormatter('agent_id','agent_id');
		}

		// $grid->add('View',null,'grid_buttons')->set('From ' . date('01-m-Y',strtotime($_GET['from_date'])). ' to ' . date('t-m-Y',strtotime($_GET['to_date'])) );
		$grid->addPaginator(500);
		$grid->addTotals(array('sum_net_commission'));
		$grid->addSno();

		$grid->removeColumn('PanNo');
		$grid->removeColumn('created_at');
		$grid->removeColumn('month');


		// $grid->addOrder()->move('deposit','before','dr_sum')->now();

		if($form->isSubmitted()){
			$grid->js()->reload(array(
					'filter'=>1,
					'from_date'=>$form['from_date']?:0,
					'agent'=>$form['agent']?:0,
					'to_date'=>$form['to_date']?:0,
					// 'account_type'=>$form['account_type']
				))->execute();
		}

	}

}

		// $grid->addColumn('net_amount');
