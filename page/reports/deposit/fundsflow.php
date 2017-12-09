<?php

class page_reports_deposit_fundsflow extends Page {
	public $title="Funds Flow Report";
	function init(){
		parent::init();

		// TRA_ACCOUNT_OPEN_AGENT_COMMISSION, TRA_PREMIUM_AGENT_COMMISSION_DEPOSIT
		// TRA_PREMIUM_AGENT_COMMISSION_DEPOSIT

		$this->app->stickyGET('from_date');
		$this->app->stickyGET('to_date');
 
		$form=$this->add('Form');
		$form->addField('DatePicker','from_date')->validateNotNull();
		$form->addField('DatePicker','to_date')->validateNotNull();

		$account_type_array=array('%'=>'All','DDS'=>'DDS','FixedAndMis'=>'Fixed And Mis','Recurring'=>'Recurring','SavingAndCurrent'=>'Saving And Current');

		$form->addField('dropdown','account_type')->setValueList($account_type_array);
		$form->addSubmit('GET List');

		$grid=$this->add('Grid_AccountsBase');
		$grid->add('H3',null,'grid_buttons')->set('Funds Flow Report From ' . date('01-m-Y',strtotime($_GET['from_date'])). ' to ' . date('t-m-Y',strtotime($_GET['to_date'])) );

		$account_model = $this->add('Model_Account');
		$account_model->addExpression('sum_debit')->set(function($m,$q){
			return $m->add('Model_TransactionRow')
					->addCondition('account_id',$q->getField('id'))
					->addCondition('created_at','>=',$_GET['from_date'])
					->addCondition('created_at','<',$this->app->nextDate($_GET['to_date']))
					->addCondition('transaction_type',[TRA_DDS_ACCOUNT_AMOUNT_WITHDRAWL,TRA_FD_ACCOUNT_AMOUNT_WITHDRAWL,TRA_RECURRING_ACCOUNT_AMOUNT_WITHDRAWL,TRA_SAVING_ACCOUNT_AMOUNT_WITHDRAWL])
					->sum('amountDr');
		})->caption('Out Flow');

		$account_model->addExpression('sum_credit')->set(function($m,$q){
			return $m->add('Model_TransactionRow')
					->addCondition('account_id',$q->getField('id'))
					->addCondition('created_at','>=',$_GET['from_date'])
					->addCondition('created_at','<',$this->app->nextDate($_GET['to_date']))
					->addCondition('transaction_type',[TRA_DDS_ACCOUNT_AMOUNT_DEPOSIT,TRA_FIXED_ACCOUNT_DEPOSIT,TRA_RECURRING_ACCOUNT_AMOUNT_DEPOSIT,TRA_SAVING_ACCOUNT_AMOUNT_DEPOSIT])
					->sum('amountCr');
		})->caption('In Flow');
		
		$account_model->addExpression('pan')->set(function($m,$q){
				return $m->refSQL('member_id')->fieldQuery('PanNo');
		});


		if($_GET['filter']){
			$this->api->stickyGET('filter');

			// if($_GET['from_date']){
			// 	$this->api->stickyGET("from_date");
			// 	$account_model->addCondition('created_at','>=',$_GET['from_date']);
			// }

			// if($_GET['to_date']){
			// 	$this->api->stickyGET("to_date");
			// 	$account_model->addCondition('created_at','<',$this->app->nextDate($_GET['to_date']));
			// }  

			if($_GET['account_type']){
				$this->api->stickyGET("account_type");
				$account_model->addCondition('SchemeType','like',$_GET['account_type']);
			}

		}else
			$account_model->addCondition('id',-1);

		$account_model->addCondition([['sum_credit','>',0],['sum_debit','>',0]]);

		$grid->addSno();

		$account_model->add('Controller_Acl');

		$grid->setModel($account_model,array('AccountNumber','member','pan','agent','sum_debit','sum_credit'));
		$grid->addTotals(['sum_credit','sum_debit']);
		$grid->addPaginator(500);

		$grid->addFormatter('member','wrap');
		$grid->addFormatter('agent','wrap');

		if($form->isSubmitted()){
			$grid->js()->reload(array('agent'=>$form['agent'],'to_date'=>$form['to_date']?:0,'from_date'=>$form['from_date']?:0,'account_type'=>$form['account_type'],'filter'=>1))->execute();
		}

	}
}