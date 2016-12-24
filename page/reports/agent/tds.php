<?php


class page_reports_agent_tds extends Page {
	public $title='Agent TDS Report';


	function init(){
		parent::init();

		set_time_limit(100);

		$till_date = $this->api->today;
		$from_date = '01-01-1970';
		if($_GET['to_date']) $till_date = $_GET['to_date'];
		if($_GET['from_date']) $from_date = $_GET['from_date'];

		$form = $this->add('Form');
		$agent_field=$form->addField('autocomplete/Basic','agent')->validateNotNull();
		$agent_field->setModel('Agent');
		$form->addField('DatePicker','from_date');
		$form->addField('DatePicker','to_date');
		$form->addField('DropDown','account_type')->setValueList(array('%'=>'All','DDS'=>'DDS','Recurring'=>'Recurring','FD'=>'FD','MIS'=>'MIS'));

		$form->addSubmit('Go');

		$grid = $this->add('Grid_AccountsBase');
		$grid->add('H3',null,'grid_buttons')->set('Agent TDS Report FROM '. date('d-M-Y',strtotime($from_date)) .' to '. date('d-M-Y',strtotime($till_date))); 

		$model = $this->add('Model_Transaction');
		$model->addCondition('transaction_type',array(TRA_ACCOUNT_OPEN_AGENT_COMMISSION,TRA_PREMIUM_AGENT_COMMISSION_DEPOSIT,TRA_PREMIUM_AGENT_COLLECTION_CHARGE_DEPOSIT));

		$reference_account_j = $model->join('accounts','reference_id');
		$agent_j=$reference_account_j->join('agents','agent_id');
		// $agent_j->addField('agent_saving_account_id','account_id');

		$reference_account_j->addField('agent_id');
		$reference_account_j->addField('account_type');
		$agent_member_j = $agent_j->join('members','member_id');
		$agent_member_j->addField('agent_name','name');
		// $agent_member_j->addField('PanNo'); // must be of the agent who you are viewing ... not who brought this account

		$model->addExpression('my_saving_account_count')->set(function($m,$q){
			$tr = $m->add('Model_TransactionRow',['table_alias'=>'sv_acc']);
			$tr->addExpression('account_type')->set(function($m,$q){
				return $m->refSQL('account_id')->fieldQuery('account_type');
			});
			$tr->addCondition('account_type','Saving');
			$tr->addCondition('transaction_id',$q->getField('id'));
			return $tr->count();
		});

		$model->addExpression('my_saving_account_id')->set(function($m,$q){
			$tr = $m->add('Model_TransactionRow',['table_alias'=>'sv_acc']);
			$tr->addExpression('account_type')->set(function($m,$q){
				return $m->refSQL('account_id')->fieldQuery('account_type');
			});
			$tr->addCondition('account_type','Saving');
			$tr->addCondition('transaction_id',$q->getField('id'));
			return $tr->setLimit(1)->fieldQuery('account_id');
		});

		$model->addExpression('my_saving_account')->set(function($m,$q){
			$tr = $m->add('Model_TransactionRow',['table_alias'=>'sv_acc']);
			$tr->addExpression('account_type')->set(function($m,$q){
				return $m->refSQL('account_id')->fieldQuery('account_type');
			});
			$tr->addCondition('account_type','Saving');
			$tr->addCondition('transaction_id',$q->getField('id'));
			return $tr->fieldQuery('account');
		});

		$model->addExpression('bandd_saving_account_id')->set(function($m,$q){
			$tr = $m->add('Model_TransactionRow',['table_alias'=>'bandd_acc']);
			$tr->addExpression('account_type')->set(function($m,$q){
				return $m->refSQL('account_id')->fieldQuery('account_type');
			});

			$tr->addCondition('account_type','Saving');
			$tr->addCondition('transaction_type_id',$q->getField('transaction_type_id'));
			$tr->addCondition('reference_id',$q->getField('reference_id'));

			// THIS IS SOOO WRONG, BUT THIS IS ONLY OPTION FOR NOW, WILL GIVE WRONG REPORT IF ANY ENTRY WILL BE EDITTED
			$tr->addCondition('id','<>',$q->getField('id'));
			$tr->addCondition('branch_id','<>',$q->getField('branch_id'));
			$tr->addCondition('created_at',$q->getField('created_at'));
			
			// SETLIMIT 1 Value
			return $tr->setLimit(1)->fieldQuery('account_id');
			// GROUP CONCAT VALUES
			return $tr->_dsql()->del('fields')->field($q->expr('(GROUP_CONCAT([0]))',[$tr->getElement('account_id')]));
		});

		$model->addExpression('bandd_saving_account')->set(function($m,$q){
			$tr = $m->add('Model_TransactionRow',['table_alias'=>'bandd_acc']);
			$tr->addExpression('account_type')->set(function($m,$q){
				return $m->refSQL('account_id')->fieldQuery('account_type');
			});

			$tr->addCondition('account_type','Saving');
			$tr->addCondition('transaction_type_id',$q->getField('transaction_type_id'));
			$tr->addCondition('reference_id',$q->getField('reference_id'));

			// THIS IS SOOO WRONG, BUT THIS IS ONLY OPTION FOR NOW, WILL GIVE WRONG REPORT IF ANY ENTRY WILL BE EDITTED
			$tr->addCondition('id','<>',$q->getField('id'));
			$tr->addCondition('branch_id','<>',$q->getField('branch_id'));
			$tr->addCondition('created_at',$q->getField('created_at'));
			
			// SET LIMIT 1 VALUE
			return $tr->setLimit(1)->fieldQuery('account');
			// GROU PCONCAT VALUE
			return $tr->_dsql()->del('fields')->field($q->expr('(GROUP_CONCAT([0]))',[$tr->getElement('account')]));
		});

		$model->addExpression('tds')->set($model->refSQL('TransactionRow')->addCondition('account','like','%TDS%')->sum('amountCr'));

		// $model->setOrder('id','desc');
		// $model->setLimit(100);
		// $temp_grid = $this->add('Grid');
		// $temp_grid->setModel($model,['transaction_type','branch','voucher_no','dr_sum','cr_sum','saving_account_id','tds']);
		// return;
		


		// $model->addExpression('total_commission')->set($model->fieldQuery('cr_sum'));
		// $model->addExpression('tds')->set('"0"');
		$model->addExpression('tr_row_count')->set($model->refSQL('TransactionRow')->count());
		$model->addExpression('net_commission')->set($model->refSQL('TransactionRow')->addCondition('account','not like','%TDS%')->sum('amountCr'));

		$model->getElement('reference_id')->caption('Account');
		$model->getElement('dr_sum')->caption('Total Amount');

		// $model->add('Controller_Acl');


		if($_GET['filter']){
			$this->api->stickyGET("filter");
			$this->api->stickyGET("from_date");
			$this->api->stickyGET("to_date");
			$this->api->stickyGET("account_type");
			$this->api->stickyGET("agent");


			if($_GET['account_type']){
				$model->addCondition('account_type','like',$_GET['account_type']);
			}

			if($_GET['from_date']){
				$model->addCondition('created_at','>=',$_GET['from_date']);
			}

			if($_GET['to_date']){
				$model->addCondition('created_at','<',$this->api->nextDate($_GET['to_date']));
			}
			if($_GET['agent']){
				$agent_temp_m = $this->add('Model_Agent');
				$agent_temp_m->load($_GET['agent']);

				$model->addCondition([
					['my_saving_account_id',$agent_temp_m['account_id']],
					['bandd_saving_account_id',$agent_temp_m['account_id']]
				]);
			}
		}else
			$model->addCondition('id',-1);

		$model->addCondition('dr_sum','>',0);
		$model->addCondition('tr_row_count',3);
		$model->getElement('created_at')->caption('Deposit Date');
		$model->setOrder('created_at');


		// $model->setLimit(10);
		// $model->_dsql()->group('reference_id');
		$grid->setModel($model,array('id','agent_id','agent','voucher_no','reference','dr_sum','tds','net_commission','my_saving_account_id','my_saving_account_count','bandd_saving_account_id','created_at','PanNo'));
		
		$grid->removeColumn('my_saving_account_id');
		$grid->removeColumn('my_saving_account_count');
		$grid->removeColumn('bandd_saving_account_id');
		
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
		$grid->addPaginator(500);
		$grid->addTotals(array('deposit','tds','dr_sum','net_commission'));
		$grid->addSno();


		$grid->addMethod('format_deposit',function($g,$f){
			$g->current_row[$f] = $g->add('Model_TransactionRow')
									->addCondition('account_id',$g->model['reference_id'])
									// ->addCondition('created_at',$g->model['created_at'])
									->addCondition('amountCr','>',0)
									->tryLoadAny()
									->get('amountCr')
									;
		});

		// $grid->addMethod('format_total_commission',function($g,$f){
		// 	$comm = $g->model['PanNo']?$g->model['net_commission']*1.111111111: $g->model['net_commission']*1.25;
		// 	$g->current_row[$f] = round($comm,2);
		// 	$g->format_float($f);
		// });
		// $grid->addFormatter('dr_sum','total_commission');

		// $grid->addMethod('format_tds',function($g,$f){
		// 	$comm = $g->current_row['dr_sum'] - $g->model['net_commission'];
		// 	$g->current_row[$f] = round($comm,2);
		// 	$g->format_float($f);
		// });
		// $grid->addFormatter('tds','tds');

		$grid->removeColumn('PanNo');

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