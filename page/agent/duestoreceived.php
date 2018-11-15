<?php

class page_agent_duestoreceived extends Page {
	public $title="Dues To Received";
	function init(){
		parent::init();

		$form=$this->add('Form');
		// $agent_field=$form->addField('autocomplete/Basic','agent');
		// $agent_field->setModel('Agent');

		$form->addField('DatePicker','from_date')->validateNotNull();
		$form->addField('DatePicker','to_date')->validateNotNull();
		// $form->addField('DatePicker','on_date');
		// $form->addField('dropdown','report_type')->setValueList(array('duelist'=>'Due List','time_collapse'=>'Time Collapse'))->setEmptyText('Please Select')->validateNotNull();
		// $form->addField('dropdown','type')->setValueList(array('RD'=>'RD'))->setEmptyText('Please Select');
		$form->addSubmit('GET List');

		$grid=$this->add('Grid_AccountsBase');
		$grid->add('H3',null,'grid_buttons')->set('Deposit Emi Due List As On ' . date('d-m-Y',strtotime($_GET['on_date']?:$this->api->today)) );
		
		$account_model=$this->add('Model_Active_Account_Recurring');
		$account_model->addCondition('agent_id',$this->api->auth->model->id);
		$member_join=$account_model->join('members','member_id');
		$member_join->addField('member_name','name');
		$member_join->addField('FatherName');
		$member_join->addField('PhoneNos');
		$member_join->addField('CurrentAddress');

		$account_model->addCondition('DefaultAC',false);
		$account_model->addCondition('MaturedStatus',false);
		
		$account_model->addExpression('paid_premium_count')->set(function($m,$q){
			return $m->refSQL('Premium')
						->addCondition('PaidOn','<>',null)
						// ->addCondition('DueDate','>',$_GET['from_date']?:'1970-01-01')
						->addCondition('PaidOn','>=',$_GET['from_date']?$m->api->nextDate($_GET['from_date']):$m->api->nextDate($m->api->today))
						->addCondition('PaidOn','<',$_GET['to_date']?$m->api->nextDate($_GET['to_date']):$m->api->nextDate($m->api->today))
						->count();
		})->sortable(true);

		$account_model->addExpression('due_premium_count')->set(function($m,$q){
			$dpc_m = $m->add('Model_Premium',array('table_alias'=>'due_premium_count_table'));
			// ->addCondition('DueDate','>',$_GET['from_date']?:'1970-01-01')
			$dpc_m->addCondition('DueDate','>=',$_GET['from_date']?$m->api->nextDate($_GET['from_date']):$m->api->nextDate($m->api->today));
			$dpc_m->addCondition('DueDate','<=',$_GET['to_date']?$m->api->nextDate($_GET['to_date']):$m->api->nextDate($m->api->today));
			$dpc_m->addCondition('account_id',$q->getField('id'));

			// Currently Paid but at that time that was Due
			$dpc_m->_dsql()->where("(PaidOn is null OR PaidOn > '". ($_GET['to_date']?:$m->api->today) ."')");
			// $dpc_m->addCondition('PaidOn','>',$_GET['on_date']?$m->api->nextDate($_GET['on_date']):$m->api->nextDate($m->api->today));
			return $dpc_m->count();
		})->sortable(true);

		$account_model->addExpression('last_premium')->set(function($m,$q){
			return $m->RefSQL('Premium')->setOrder('id','desc')->setLimit(1)->fieldQuery('DueDate');
		});

		$account_model->addExpression('premium_amount')->set(function($m,$q){
			return $m->RefSQL('Premium')->setOrder('id','desc')->setLimit(1)->fieldQuery('Amount');
		});

		$account_model->addExpression('total')->set(function ($m,$q){
			$dpc_m = $m->add('Model_Premium',array('table_alias'=>'due_premium_amount'));
			// ->addCondition('DueDate','>',$_GET['from_date']?:'1970-01-01')
			$dpc_m->addCondition('DueDate','>=',$_GET['from_date']?$m->api->nextDate($_GET['from_date']):$m->api->nextDate($m->api->today));
			$dpc_m->addCondition('DueDate','<=',$_GET['to_date']?$m->api->nextDate($_GET['to_date']):$m->api->nextDate($m->api->today));
			$dpc_m->addCondition('account_id',$q->getField('id'));

			// Currently Paid but at that time that was Due
			$dpc_m->_dsql()->where("(PaidOn is null OR PaidOn > '". ($_GET['to_date']?:$m->api->today) ."')");
			// $dpc_m->addCondition('PaidOn','>',$_GET['on_date']?$m->api->nextDate($_GET['on_date']):$m->api->nextDate($m->api->today));
			return $dpc_m->sum('Amount');
		});
		

		$account_model->addExpression('agent')->set($account_model->refSQL('agent_id')->fieldQuery('name'));
		$account_model->addExpression('agent_code')->set($account_model->refSQL('agent_id')->fieldQuery('AgentCode'));
		$account_model->addExpression('agent_phone')->set($this->add('Model_Member')->addCondition('id',$account_model->refSQL('agent_id')->fieldQuery('member_id'))->fieldQuery('PhoneNos'));

		$account_agent = $account_model->refSQL('agent_id');


		if($_GET['filter']){
			$this->api->stickyGET('filter');
			// ALREADY IMPLEMENTED IN EXPRESSIONS
			// if($_GET['agent']){
			// 	$this->api->stickyGET('agent');
			// 	$account_model->addCondition('agent_id',$_GET['agent']);
			// }

			// $this->api->stickyGET('report_type');
			$this->api->stickyGET('from_date');
			$this->api->stickyGET('to_date');

			// if($_GET['from_date'])
			// 	$account_model->addCondition('created_at','>=',$_GET['on_date']);
			// if($_GET['to_date'])
			// 	$account_model->addCondition('created_at','<=',$_GET['on_date']);

		}else
			$account_model->addCondition('id',-1);

		$account_model->addCondition('due_premium_count','>',0);
		// $account_model->add('Controller_Acl');
		$grid->setModel($account_model,array('AccountNumber','created_at','member_name','FatherName','CurrentAddress','PhoneNos','due_premium_count','premium_amount','total','agent','agent_code','agent_phone'));
		$grid->addSno();
		// $grid->removeColumn('last_premium');

		// $grid->addMethod('format_balance',function($g,$f){
		// 	$bal = $g->model->getOpeningBalance($on_date=$_GET['on_date']?:$g->api->today,$side='both',$forPandL=false);
		// 	$bal = $bal['Cr'] - $bal['Dr'];
		// 	$g->current_row[$f] = $bal .' Cr';
		// });

		// $grid->addColumn('balance','balance');

		$grid->addPaginator(500);
		$grid->addTotals(['total']);

		// $js=array(
		// 	$this->js()->_selector('.mymenu')->parent()->parent()->toggle(),
		// 	$this->js()->_selector('#header')->toggle(),
		// 	$this->js()->_selector('#footer')->toggle(),
		// 	$this->js()->_selector('ul.ui-tabs-nav')->toggle(),
		// 	$this->js()->_selector('.atk-form')->toggle(),
		// 	);

		// $grid->js('click',$js);

		if($form->isSubmitted()){
			$grid->js()->reload(array('agent'=>$form['agent'],'to_date'=>$form['to_date']?:0,'from_date'=>$form['from_date']?:0,'report_type'=>$form['report_type'],'filter'=>1))->execute();
		}	

	}
}