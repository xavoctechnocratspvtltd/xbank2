<?php

class page_reports_deposit_emiduelist extends Page {
	public $title="Deposit EMI Due List";
	function init(){
		parent::init();

		$form=$this->add('Form');
		$agent_field=$form->addField('autocomplete/Basic','agent');
		$agent_field->setModel('Agent');

		$form->addField('DatePicker','from_date');
		$form->addField('DatePicker','to_date');
		$form->addField('dropdown','report_type')->setValueList(array('duelist'=>'Due List','time_collapse'=>'Time Collapse'))->setEmptyText('Please Select');
		$form->addField('dropdown','type')->setValueList(array('RD'=>'RD','DDS'=>'DDS'))->setEmptyText('Please Select');
		$form->addSubmit('GET List');

		$grid=$this->add('Grid');
		$grid->add('H3',null,'grid_buttons')->set('Deposit Emi Due List From ' . date('01-m-Y',strtotime($_GET['from_date'])). ' to ' . date('t-m-Y',strtotime($_GET['to_date'])) );
		$account_model=$this->add('Model_Active_Account_Recurring');
		$member_join=$account_model->join('members','member_id');
		$member_join->addField('member_name','name');
		$member_join->addField('FatherName');
		$member_join->addField('PhoneNos');
		$member_join->addField('CurrentAddress');

		$account_model->addCondition('DefaultAC',false);
		
		$account_model->addExpression('paid_premium_count')->set(function($m,$q){
			return $m->refSQL('Premium')
						->addCondition('PaidOn','<>',null)
						// ->addCondition('DueDate','>',$_GET['from_date']?:'1970-01-01')
						->addCondition('PaidOn','<=',$_GET['to_date']?$m->api->nextDate($_GET['to_date']):$m->api->nextDate($m->api->today))
						->count();
		})->sortable(true);

		$account_model->addExpression('due_premium_count')->set(function($m,$q){
			$dpc_m = $m->add('Model_Premium',array('table_alias'=>'due_premium_count_table'));
			// ->addCondition('DueDate','>',$_GET['from_date']?:'1970-01-01')
			$dpc_m->addCondition('DueDate','<=',$_GET['to_date']?$m->api->nextDate($_GET['to_date']):$m->api->nextDate($m->api->today));
			$dpc_m->addCondition('account_id',$q->getField('id'));
			$dpc_m->_dsql()->where("(PaidOn is null OR PaidOn > '". ($_GET['to_date']?:$m->api->today) ."')");
			// $dpc_m->addCondition('PaidOn','>',$_GET['to_date']?$m->api->nextDate($_GET['to_date']):$m->api->nextDate($m->api->today));
			return $dpc_m->count();
		});

		$account_model->addExpression('last_premium')->set(function($m,$q){
			return $m->RefSQL('Premium')->setOrder('id','desc')->setLimit(1)->fieldQuery('DueDate');
		});

		$account_model->addExpression('premium_amount')->set(function($m,$q){
			return $m->RefSQL('Premium')->setOrder('id','desc')->setLimit(1)->fieldQuery('Amount');
		});

		$account_model->addExpression('guarantor_name')->set(function($m,$q){
			$guarantor_m = $m->add('Model_Member',array('table_alias'=>'guarantor_name_q'));
			$ac_join = $guarantor_m->join('account_guarantors.member_id');
			$ac_join->addField('account_id');
			$guarantor_m->addCondition('account_id',$q->getField('id'));
			$guarantor_m->setLimit(1);
			$guarantor_m->setOrder('id');
			return $guarantor_m->_dsql()->del('fields')->field($guarantor_m ->table_alias.'.name');
		});

		if($_GET['filter']){
			$this->api->stickyGET('filter');
			// ALREADY IMPLEMENTED IN EXPRESSIONS
			if($_GET['agent']){
				$this->api->stickyGET('agent');
				$account_model->addCondition('agent_id',$_GET['agent']);
			}

			$this->api->stickyGET('report_type');

			switch ($_GET['report_type']) {
				case 'duelist':
					$account_model->addCondition('last_premium','<',$this->api->nextDate($_GET['to_date']?:$this->api->today));
					break;
				case 'time_collapse':
					$account_model->addCondition('last_premium','>',$_GET['last_premium']);
					# code...
					break;
				

				default:
					# code...
					break;
			}

			if($_GET['from_date'])
				$account_model->addCondition('created_at','>=',$_GET['from_date']);
			if($_GET['to_date'])
				$account_model->addCondition('created_at','<=',$_GET['to_date']);

			if($_GET['account_type']){
				$account_model->addCondition('account_type',$_GET['account_type']);
			}

		}else
			$account_model->addCondition('id',-1);

		$account_model->add('Controller_Acl');
		$grid->setModel($account_model,array('AccountNumber','created_at','member_name','FatherName','CurrentAddress','PhoneNos','paid_premium_count','due_premium_count','premium_amount','guarantor_name','last_premium'));

		$grid->addMethod('format_balance',function($g,$f){
			$bal = $g->model->getOpeningBalance($on_date=$_GET['to_date']?:$g->api->today,$side='both',$forPandL=false);
			$bal = $bal['Cr'] - $bal['Dr'];
			$g->current_row[$f] = $bal .' Cr';
		});

		$grid->addColumn('balance','balance');

		$grid->addPaginator(50);

		$js=array(
			$this->js()->_selector('.mymenu')->parent()->parent()->toggle(),
			$this->js()->_selector('#header')->toggle(),
			$this->js()->_selector('#footer')->toggle(),
			$this->js()->_selector('ul.ui-tabs-nav')->toggle(),
			$this->js()->_selector('.atk-form')->toggle(),
			);

		$grid->js('click',$js);

		if($form->isSubmitted()){
			$grid->js()->reload(array('agent'=>$form['agent'],'from_date'=>$form['from_date']?:0,'to_date'=>$form['to_date']?:0,'report_type'=>$form['report_type'],'filter'=>1))->execute();
		}	
	}
}