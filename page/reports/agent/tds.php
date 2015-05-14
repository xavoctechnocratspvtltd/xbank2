<?php


class page_reports_agent_tds extends Page {
	public $title='Agent TDS Report';


	function init(){
		parent::init();

		$till_date = $this->api->today;
		if($_GET['to_date']) $till_date = $_GET['to_date'];

		$form = $this->add('Form');
		$agent_field=$form->addField('autocomplete/Basic','agent');
		$agent_field->setModel('Agent');
		$form->addField('DatePicker','from_date');
		$form->addField('DatePicker','to_date');
		$form->addField('DropDown','account_type')->setValueList(array('%'=>'All','DDS'=>'DDS','Recurring'=>'Recurring','FD'=>'FD','MIS'=>'MIS'));

		$form->addSubmit('Go');

		$grid = $this->add('Grid_AccountsBase');
		$grid->add('H3',null,'grid_buttons')->set('Agent TDS Report As On '. date('d-M-Y',strtotime($till_date))); 

		$model = $this->add('Model_Transaction');
		$model->addCondition('transaction_type',array(TRA_ACCOUNT_OPEN_AGENT_COMMISSION,TRA_PREMIUM_AGENT_COMMISSION_DEPOSIT,TRA_PREMIUM_AGENT_COLLECTION_CHARGE_DEPOSIT));

		$reference_account_j = $model->join('accounts','reference_id');
		$reference_account_j->addField('agent_id');
		$reference_account_j->addField('account_type');

		// $model->addExpression('total_commission')->set($model->fieldQuery('cr_sum'));
		$model->addExpression('tds')->set($model->refSQL('TransactionRow')->addCondition('account','like','%TDS%')->sum('amountCr'));
		$model->addExpression('net_commission')->set($model->refSQL('TransactionRow')->addCondition('account','not like','%TDS%')->sum('amountCr'));

		$model->getElement('reference_id')->caption('Account');
		$model->getElement('dr_sum')->caption('Total Amount');


		if($_GET['filter']){
			$this->api->stickyGET("filter");
			$this->api->stickyGET("from_date");
			$this->api->stickyGET("to_date");
			$this->api->stickyGET("account_type");
			$this->api->stickyGET("agent");

			if($_GET['account_type']){
				$model->addCondition('account_type','like',$_GET['account_type']);
			}

			if($_GET['from_date'])
				$model->addCondition('created_at','>=',date('Y-m-01',strtotime($_GET['from_date'])));

			if($_GET['to_date'])
				$model->addCondition('created_at','<=',date('Y-m-t',strtotime($_GET['to_date'])));
			if($_GET['agent'])
				$model->addCondition('agent_id',$_GET['agent']);
		}else
			$model->addCondition('id',-1);

		$model->getElement('created_at')->caption('Deposit Date');
		
		// $model->setLimit(10);
		$model->_dsql()->group('reference_id');
		$grid->setModel($model,array('agent_id','agent','voucher_no','reference','dr_sum','tds','net_commission','created_at'));
		
		if($_GET['agent'])
			$grid->removeColumn('agent_id');
		else{
			$grid->addMethod('format_agent_id',function($g,$f){
				if($g->model['agent_id']){
					$agent_m = $g->add('Model_Agent')->tryLoad($g->model['agent_id']);				
					$g->current_row[$f] = $agent_m['agent_member_name'];
				}
				
			});
			$grid->addFormatter('agent_id','agent_id');
		}

		$grid->add('View',null,'grid_buttons')->set('From ' . date('01-m-Y',strtotime($_GET['from_date'])). ' to ' . date('t-m-Y',strtotime($_GET['to_date'])) );
		$grid->addPaginator(50);
		$grid->addSno();

		$grid->addMethod('format_deposit',function($g,$f){
			$g->current_row[$f] = $g->add('Model_TransactionRow')
									->addCondition('account_id',$g->model['reference_id'])
									->addCondition('created_at',$g->model['created_at'])
									->addCondition('amountCr','>',0)
									->tryLoadAny()
									->get('amountCr')
									;
		});

		$grid->addColumn('deposit','deposit');
		$grid->addOrder()->move('deposit','before','dr_sum')->now();

		if($form->isSubmitted()){
			$grid->js()->reload(array(
					'filter'=>1,
					'from_date'=>$form['from_date']?:0,
					'agent'=>$form['agent']?:0,
					'to_date'=>$form['to_date']?:0,
					'account_type'=>$form['account_type']
				))->execute();
		}

	}

}