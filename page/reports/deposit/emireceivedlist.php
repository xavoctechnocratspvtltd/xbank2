<?php

class page_reports_deposit_emireceivedlist extends Page {
	public $title="Deposit Premium Received List";
	function init(){
		parent::init();


		$till_date = $this->api->today;

		if($_GET['to_date']){
			$till_date=$_GET['to_date'];
		}

		$form=$this->add('Form');

		$agent_field=$form->addField('autocomplete/Basic','agent');
		$agent_field->setModel('Agent');
		
		$form->addField('Checkbox','without_agent');
		$form->addField('DatePicker','from_date');
		$form->addField('DatePicker','to_date');
		$form->addField('dropdown','account_type')->setValueList(array('%'=>'All','Recurring'=>'RD','DDS'=>'DDS'));

		$form->addSubmit('GET List');

		$grid=$this->add('Grid_AccountsBase');
		$grid->add('H3',null,'grid_buttons')->set('Deposit Premium Received List As On '. date('d-M-Y',strtotime($till_date))); 

		$transaction_row_model=$this->add('Model_TransactionRow');
		
		$transaction_join = $transaction_row_model->join('transactions','transaction_id');
		$transaction_type_join = $transaction_join->join('transaction_types','transaction_type_id');
		$account_join = $transaction_row_model->join('accounts','account_id');
		$account_join->addField('agent_id');
		
		$account_member_join = $account_join->join('members','member_id');
		$dealer_join = $account_join->leftJoin('dealers','dealer_id');
		$scheme_join = $account_join->join('schemes','scheme_id');

		$dealer_join->addField('dealer_name','name');
		$account_member_join->addField('member_name','name');
		$account_member_join->addField('FatherName');
		$account_member_join->addField('phone_no','PhoneNos');
		$account_member_join->addField('CurrentAddress','CurrentAddress');
		$account_member_join->addField('landmark');
		$account_member_join->addField('pan_no','PanNo');

		$agent_join=$account_join->leftJoin('agents','agent_id');
		$agent_member_join = $agent_join->leftJoin('members','member_id');
		$agent_member_join->addField('agent_name','name');

		$agent_sb_join = $agent_join->leftJoin('accounts','account_id');
		$agent_sb_join->addField('agent_account_number','AccountNumber');
		$account_join->addField('AccountNumber');
		$account_join->addField('account_type');
		$account_join->addField('dealer_id');
		$scheme_join->addField('SchemeType');
		$scheme_join->addField('scheme_name','name');
		$transaction_type_join->addField('transaction_type_name','name');

		$transaction_row_model->addCondition('transaction_type_name',array(TRA_RECURRING_ACCOUNT_AMOUNT_DEPOSIT,TRA_DDS_ACCOUNT_AMOUNT_DEPOSIT));
		$transaction_row_model->addCondition('amountCr','>',0);
		$transaction_row_model->addCondition(
			$transaction_row_model->dsql()->orExpr()
				->where('SchemeType','Recurring')
				->where('SchemeType','DDS')
			);

		$transaction_row_model->addExpression('due_premium_count')->set(function($m,$q)use($till_date){
			$dpc_m = $m->add('Model_Premium',array('table_alias'=>'due_premium_count_table'));
			// ->addCondition('DueDate','>',$_GET['from_date']?:'1970-01-01')
			$dpc_m->addCondition('DueDate','<',$m->api->nextDate($till_date));
			$dpc_m->addCondition('account_id',$q->getField('account_id'));
			$dpc_m->_dsql()->where("(PaidOn is null OR PaidOn >= '". ($m->api->nextDate($till_date)) ."')");
			// $dpc_m->addCondition('PaidOn','>',$_GET['on_date']?$m->api->nextDate($_GET['on_date']):$m->api->nextDate($m->api->today));
			return $dpc_m->count();
		})->sortable(true);

		$transaction_row_model->addExpression('last_paid_on')->set(function($m,$q)use($till_date){
			$dpc_m = $m->add('Model_Premium',array('table_alias'=>'last_paid_on'));
			// ->addCondition('DueDate','>',$_GET['from_date']?:'1970-01-01')
			$dpc_m->addCondition('account_id',$q->getField('account_id'));
			$dpc_m->setOrder('id','desc');
			$dpc_m->setLimit(1);
			$dpc_m->_dsql()->where("(PaidOn is not null)");
			// $dpc_m->addCondition('PaidOn','>',$_GET['on_date']?$m->api->nextDate($_GET['on_date']):$m->api->nextDate($m->api->today));
			return $dpc_m->fieldQuery('PaidOn');
		})->sortable(true);

		$transaction_row_model->getElement('amountCr')->caption('Amount Deposited');

		$transaction_row_model->setOrder('account_id desc,created_at desc');
		
		if($_GET['filter']){
			$this->api->stickyGET("filter");
			$this->api->stickyGET("account_type");

			if($_GET['from_date']){
				$this->api->stickyGET("from_date");
				$transaction_row_model->addCondition('created_at','>=',$_GET['from_date']);
			}

			if($_GET['to_date']){
				$this->api->stickyGET("to_date");
				$transaction_row_model->addCondition('created_at','<',$this->api->nextDate($_GET['to_date']));
			}

			if($_GET['agent_id']){
				$this->api->stickyGET("agent_id");
				$transaction_row_model->addCondition('agent_id',$_GET['agent_id']);
			}

			if($_GET['without_agent']){
				$this->api->stickyGET("without_agent");
				$transaction_row_model->addCondition('agent_id',null);
			}

			if($_GET['account_type'])
				$transaction_row_model->addCondition('account_type','like',$_GET['account_type']);

		}else
			$transaction_row_model->addCondition('id',-1);

		$transaction_row_model->add('Controller_Acl');
		$transaction_row_model->setOrder('created_at','desc');
		$transaction_row_model->getElement('created_at')->caption('Deposited On');

		$grid->setModel($transaction_row_model,array('AccountNumber','member_name','phone_no','pan_no','FatherName','CurrentAddress','landmark','due_premium_count','amountCr','agent_name','agent_account_number','created_at','Narration','scheme_name','last_paid_on'));
		// $grid->removeColumn('CurrentAddress');
		$grid->addFormatter('CurrentAddress','Wrap');
		$grid->addFormatter('FatherName','100Wrap');
		$grid->addTotals(array('amountCr'));
		$grid->addSno();

		// $js=array(
		// 	$this->js()->_selector('.mymenu')->parent()->parent()->toggle(),
		// 	$this->js()->_selector('#header')->toggle(),
		// 	$this->js()->_selector('#footer')->toggle(),
		// 	$this->js()->_selector('ul.ui-tabs-nav')->toggle(),
		// 	$this->js()->_selector('.atk-form')->toggle(),
		// 	);

		// $grid->js('click',$js);

		if($form->isSubmitted()){
			$grid->js()->reload(array('account_type'=>$form['account_type'],'to_date'=>$form['to_date']?:0,'from_date'=>$form['from_date']?:0,'agent_id'=>$form['agent'],'without_agent'=>$form['without_agent']?:0,'filter'=>1))->execute();
		}
	}
}