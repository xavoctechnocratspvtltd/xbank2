<?php

class page_dashboard_cash extends Page {

	public $bank_od_updatetotal=0;
	public $cash_updatetotal=0;
	function init(){
		parent::init();

		$heading = $this->add('H2')->set(array('Bank Report','icon'=>'flag'));

		$grid=$this->add('Grid_AccountsBase');

		$grid->receive_sum=0;
		$grid->paid_sum=0;
		$grid->balance_sum=0;
		$grid->receive_view = $this->add('View')->addClass('atk-align-right');
		$grid->paid_view = $this->add('View')->addClass('atk-align-right');
		$grid->balance_view = $this->add('View')->addClass('atk-align-right');
		$accounts=$this->add('Model_Active_Account');
		$accounts->addCondition($accounts->dsql()->orExpr()
				->where($accounts->scheme_join->table_alias.'.name',BANK_ACCOUNTS_SCHEME)
				);

		$accounts->add('Controller_Acl');
		$grid->setModel($accounts,array('AccountNumber'));
		$grid->addSno();

		$grid->addMethod('format_opbal',function($grid,$field){
			$bal = $grid->model->getOpeningBalance($grid->api->today);
			$bal_side = (($bal['Cr'] - $bal['Dr']) > 0) ? 'Cr' : 'Dr';
			$bal_fig = abs($bal['Cr'] - $bal['Dr']);
			$grid->current_row[$field] = $bal_fig . ' ' . $bal_side;
		});

		$grid->addColumn('opbal','opening_balance');

		$grid->addMethod('format_received',function($grid,$field){
			$grid->receive_sum += round($grid->model->allDrCrSum('Dr'),2);
			$grid->current_row[$field] = round($grid->model->allDrCrSum('Dr'),2);
			$grid->receive_view->set("Total Received ".round($grid->receive_sum,2));
		});

		$grid->addColumn('received','todays_received');

		$grid->addMethod('format_paid',function($grid,$field){
			$grid->paid_sum += round($grid->model->allDrCrSum('Cr'),2);
			$grid->current_row[$field] = round($grid->model->allDrCrSum('Cr'),2);
			$grid->paid_view->set("Total Payment ".round($grid->paid_sum,2));
		});

		$grid->addColumn('paid','todays_payment');


		$grid->addMethod('format_crbal',function($grid,$field){
			$bal = $grid->model->getOpeningBalance($grid->api->nextDate($grid->api->today));
			$bal_side = (($bal['Cr'] - $bal['Dr']) > 0) ? 'Cr' : 'Dr';
			$bal_fig = abs($bal['Cr'] - $bal['Dr']);
			$grid->current_row[$field] = round($bal_fig,2) . ' ' . $bal_side;

			$grid->balance_sum += ($bal['Dr'] - $bal['Cr']);
			$grid->balance_view->set("Total Balance ".round($grid->balance_sum,2));
		});

		$grid->addColumn('crbal','balance_on_date');
		


		$heading = $this->add('H2')->set(array('Bank OD','icon'=>'flag'));

		$grid=$this->add('Grid_AccountsBase');
		$grid->receive_sum1=0;
		$grid->paid_sum1=0;
		$grid->balance_sum1=0;
		$grid->available_limit_sum1=0;
		$grid->receive_view1 = $this->add('View')->addClass('atk-align-right');
		$grid->paid_view1 = $this->add('View')->addClass('atk-align-right');
		$grid->balance_view1 = $this->add('View')->addClass('atk-align-right');
		$grid->available_limit_view1 = $this->add('View')->addClass('atk-align-right');

		$accounts=$this->add('Model_Active_Account');
		$accounts->addCondition(
				$accounts->dsql()->orExpr()
				->where($accounts->scheme_join->table_alias.'.name','Bank OD')
				);

		$accounts->add('Controller_Acl');
		$grid->setModel($accounts,array('AccountNumber','bank_account_limit'));
		$grid->addSno();

		$grid->addMethod('format_opbal',function($grid,$field){
			$bal = $grid->model->getOpeningBalance($grid->api->today);
			$bal_side = (($bal['Cr'] - $bal['Dr']) > 0) ? 'Cr' : 'Dr';
			$bal_fig = abs($bal['Cr'] - $bal['Dr']);
			$grid->current_row[$field] = $bal_fig . ' ' . $bal_side;
		});

		$grid->addColumn('opbal','opening_balance');

		$grid->addMethod('format_received',function($grid,$field){
			$grid->receive_sum1 += round($grid->model->allDrCrSum('Dr'),2);
			$grid->current_row[$field] = round($grid->model->allDrCrSum('Dr'),2);
			$grid->receive_view1->set("Total Received ".round($grid->receive_sum1,2));
		});

		$grid->addColumn('received','todays_received');

		$grid->addMethod('format_paid',function($grid,$field){
			$grid->paid_sum1 += round($grid->model->allDrCrSum('Cr'),2);
			$grid->current_row[$field] = round($grid->model->allDrCrSum('Cr'),2);
			$grid->paid_view1->set("Total Payment ".round($grid->paid_sum1,2));
		});

		$grid->addColumn('paid','todays_payment');


		$grid->addMethod('format_crbal',function($grid,$field){
			$bal = $grid->model->getOpeningBalance($grid->api->nextDate($grid->api->today));
			$bal_side = (($bal['Cr'] - $bal['Dr']) > 0) ? 'Cr' : 'Dr';
			$bal_fig = abs($bal['Cr'] - $bal['Dr']);
			$grid->current_row[$field] = round($bal_fig,2) . ' ' . $bal_side;
			$grid->balance_sum1 += ($bal['Dr'] - $bal['Cr']);
			$grid->balance_view1->set("Total Balance ".round($grid->balance_sum1,2));
		});

		$grid->addColumn('crbal','balance_on_date');

		$grid->addMethod('format_availablelimit',function($grid,$field){
			$bal = $grid->model->getOpeningBalance($grid->api->nextDate($grid->api->today));
			$op_bal = $bal['dr'] - $bal['cr'] + $grid->model['bank_account_limit'];
			
			$grid->current_row[$field] = $op_bal;
			$grid->available_limit_sum1 += $op_bal;
			$grid->available_limit_view1->set("Total Available Limit ".round($grid->available_limit_sum1,2));
		});
		$grid->addColumn('availablelimit','available_limit');

		$grid->addOrder()->move('bank_account_limit', 'before','available_limit')->now();

		$this->add('H2')->set(array('Cash Report','icon'=>'flag'));
		$grid2=$this->add('Grid_AccountsBase');
		$grid2->receive_sum2=0;
		$grid2->paid_sum2=0;
		$grid2->balance_sum2=0;
		$grid2->receive_view2 = $this->add('View')->addClass('atk-align-right');
		$grid2->paid_view2 = $this->add('View')->addClass('atk-align-right');
		$grid2->balance_view2 = $this->add('View')->addClass('atk-align-right');


		$account_cash=$this->add('Model_Account');
		$account_cash->addCondition('scheme_name',CASH_ACCOUNT);
		$account_cash->add('Controller_Acl');
		$grid2->setModel($account_cash,array('AccountNumber'));
		$grid2->addSno();

		$grid2->addMethod('format_opbal',function($grid,$field){
			$bal = $grid->model->getOpeningBalance($grid->api->today);
			$bal_side = (($bal['Cr'] - $bal['Dr']) > 0) ? 'Cr' : 'Dr';
			$bal_fig = abs($bal['Cr'] - $bal['Dr']);
			$grid->current_row[$field] = $bal_fig . ' ' . $bal_side;
		});

		$grid2->addColumn('opbal','opening_balance');

		$grid2->addMethod('format_received',function($grid,$field){
			$grid->receive_sum2 += round($grid->model->allDrCrSum('Dr'),2);
			$grid->current_row[$field] = round($grid->model->allDrCrSum('Dr'),2);
			$grid->receive_view2->set("Total Debit ".round($grid->receive_sum2,2));
		});

		$grid2->addColumn('received','Debit');

		$grid2->addMethod('format_paid',function($grid,$field){
			$grid->paid_sum2 += round($grid->model->allDrCrSum('Cr'),2);
			$grid->current_row[$field] = round($grid->model->allDrCrSum('Cr'),2);
			$grid->paid_view2->set("Total Credit ".round($grid->paid_sum2,2));
		});

		$grid2->addColumn('paid','Credit');


		$grid2->addMethod('format_crbal',function($grid,$field){
			$bal = $grid->model->getOpeningBalance($grid->api->nextDate($grid->api->today));
			$bal_side = (($bal['Cr'] - $bal['Dr']) > 0) ? 'Cr' : 'Dr';
			$bal_fig = abs($bal['Cr'] - $bal['Dr']);
			$grid->current_row[$field] = round($bal_fig,2) . ' ' . $bal_side;
			$grid->balance_sum2 += ($bal['Dr'] - $bal['Cr']);
			$grid->balance_view2->set("Total Balance ".round($grid->balance_sum2,2));
		});

		$grid2->addColumn('crbal','balance_on_date');

		
	}
}