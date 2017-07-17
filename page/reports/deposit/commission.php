<?php

class page_reports_deposit_commission extends Page {
	public $title="Commission Report";
	function init(){
		parent::init();

		// TRA_ACCOUNT_OPEN_AGENT_COMMISSION, TRA_PREMIUM_AGENT_COMMISSION_DEPOSIT
		// TRA_PREMIUM_AGENT_COMMISSION_DEPOSIT
 
		$form=$this->add('Form');
		$form->addField('DatePicker','from_date')->validateNotNull();
		$form->addField('DatePicker','to_date')->validateNotNull();

		$account_type_array=array('%'=>'All','DDS'=>'DDS','FixedAndMis'=>'Fixed And Mis','Recurring'=>'Recurring');

		$form->addField('dropdown','account_type')->setValueList($account_type_array);
		$form->addSubmit('GET List');

		$grid=$this->add('Grid_AccountsBase');
		$grid->add('H3',null,'grid_buttons')->set('Commission Report From ' . date('01-m-Y',strtotime($_GET['from_date'])). ' to ' . date('t-m-Y',strtotime($_GET['to_date'])) );

		$transaction = $this->add('Model_Transaction');
		$transaction->addExpression('commission')->set(function($m,$q){
			return $m->refSQL('TransactionRow')
				->addCondition([['account','like','%Commission%'],['account','like','%Collection%']])
				->sum('amountDr');
		});

		$transaction->addExpression('tds')->set(function($m,$q){
			return $m->refSQL('TransactionRow')
				->addCondition('account','like','%TDS%')
				->sum('amountCr');
		});

		$transaction->addExpression('acc')->set(function($m,$q){
			$tr=  $m->refSQL('TransactionRow');
			$tr->addExpression('SchemeType')->set(function($m,$q){
				return $m->refSQL('scheme_id')->fieldQuery('SchemeType');
			});
			$tr->addCondition('account','not like','%TDS%')
			->addCondition([['balance_sheet','Branch/Divisions'],['balance_sheet','Deposits - Liabilities']])
				;
				// ->addCondition([['scheme','Saving Account'],['scheme','Branch & Divisions']])
			return $tr->sum('amountCr');
		})->caption('Net Commission');

		$transaction->addExpression('acc_name')->set(function($m,$q){

			$tr=  $m->refSQL('TransactionRow');
			$tr->addExpression('SchemeType')->set(function($m,$q){
				return $m->refSQL('scheme_id')->fieldQuery('SchemeType');
			});

			return $tr
				->addCondition([['SchemeType','SavingAndCurrent'],['scheme','Branch & Divisions']])
				->fieldQuery('account');
		})->sortable(true)
		->caption('Agent Account');

		$referance_account_join = $transaction->leftjoin('accounts','reference_id');
		$referance_account_join->addField('AccountNumber')->sortable(true);
		$referance_account_join->addField('Amount');
		$reference_member_join = $referance_account_join->join('members','member_id');
		$reference_member_join->addField('member_name','name');
		$referance_account_scheme_join = $referance_account_join->join('schemes','scheme_id');
		$referance_account_scheme_join->addField('ref_account_scheme_type','SchemeType');
		$referance_account_scheme_join->addField('ref_account_scheme_name','name');



		if($_GET['filter']){
			$this->api->stickyGET('filter');

			if($_GET['from_date']){
				$this->api->stickyGET("from_date");
				$transaction->addCondition('created_at','>=',$_GET['from_date']);
			}

			if($_GET['to_date']){
				$this->api->stickyGET("to_date");
				$transaction->addCondition('created_at','<',$this->app->nextDate($_GET['to_date']));
			}  

			if($_GET['account_type']){
				$this->api->stickyGET("account_type");
				$transaction->addCondition('ref_account_scheme_type','like',$_GET['account_type']);
			}

		}else
			$transaction->addCondition('id',-1);

		$grid->addSno();

		$transaction->_dsql()->having(
            $transaction->dsql()->orExpr()
                ->where('transaction_type', TRA_ACCOUNT_OPEN_AGENT_COMMISSION)
                ->where('transaction_type', TRA_PREMIUM_AGENT_COMMISSION_DEPOSIT)
                ->where('transaction_type', TRA_PREMIUM_AGENT_COLLECTION_CHARGE_DEPOSIT)
        );

		// To filter empty rows... don't know why they are there
        $transaction->addCondition('commission','>',0);

		$transaction->setOrder('created_at','desc');
		$transaction->add('Controller_Acl');

		$grid->setModel($transaction,array('AccountNumber','member_name','Amount','ref_account_scheme_name','created_at','commission','tds','acc','acc_name','voucher_no','transaction_type'));
		$grid->addFormatter('voucher_no','voucherNo');
		$grid->addTotals(['commission','tds','acc','Amount']);

		// $grid->addMethod('format_totalCommission',function($g,$f){
		// 	$m=$g->add('Model_TransactionRow');
		// 	$m->addCondition('transaction_id',$g->model->dsql()->expr($g->model['transaction_id']));
		// 	$m->addCondition('amountDr','>',0);
			
		// 	$a_j = $m->join('accounts','account_id');
		// 	$a_j->addField('AccountNumber');

		// 	$m->addCondition('AccountNumber','like','%Commission Paid On%');

		// 	$m->setLimit(1);

		// 	$m->_dsql()->del('fields')->field('amountDr');
		// 	// $m->tryLoadAny();
		// 	$g->current_row[$f]=$m->_dsql()->getOne();
		// });

		// $grid->addMethod('format_tds',function($g,$f){
		// 	$g->current_row[$f]=$g->current_row['total_commission'] - $g->current_row['amountCr'];
		// });
		
		// $grid->addColumn('totalCommission','total_commission');
		// $grid->addColumn('tds','tds','TDS');
		$grid->addPaginator(500);
		// $grid->addTotals(array('tds'));

		$grid->removeColumn('transaction_type');

		// $grid->addOrder()->move('amountCr','after','total_commission')->now();
		
		// $grid->addColumn('expander','accounts');
		if($form->isSubmitted()){
			$grid->js()->reload(array('agent'=>$form['agent'],'to_date'=>$form['to_date']?:0,'from_date'=>$form['from_date']?:0,'account_type'=>$form['account_type'],'filter'=>1))->execute();
		}

	}
}