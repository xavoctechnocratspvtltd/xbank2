<?php

class page_dashboard_cash extends Page {
	function init(){
		parent::init();

		$heading = $this->add('H2')->set(array('Bank Report','icon'=>'flag'));

		$grid=$this->add('Grid_AccountsBase');
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
			$grid->current_row[$field] = round($grid->model->allDrCrSum('Dr'),2);
		});

		$grid->addColumn('received','todays_received');

		$grid->addMethod('format_paid',function($grid,$field){
			$grid->current_row[$field] = round($grid->model->allDrCrSum('Cr'),2);
		});

		$grid->addColumn('paid','todays_payment');


		$grid->addMethod('format_crbal',function($grid,$field){
			$bal = $grid->model->getOpeningBalance($grid->api->nextDate($grid->api->today));
			$bal_side = (($bal['Cr'] - $bal['Dr']) > 0) ? 'Cr' : 'Dr';
			$bal_fig = abs($bal['Cr'] - $bal['Dr']);
			$grid->current_row[$field] = round($bal_fig,2) . ' ' . $bal_side;
		});

		$grid->addColumn('crbal','balance_on_date');


		$heading = $this->add('H2')->set(array('Bank OD','icon'=>'flag'));

		$grid=$this->add('Grid_AccountsBase');
		$accounts=$this->add('Model_Active_Account');
		$accounts->addCondition(
				$accounts->dsql()->orExpr()
				->where($accounts->scheme_join->table_alias.'.name','Bank OD')
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
			$grid->current_row[$field] = round($grid->model->allDrCrSum('Dr'),2);
		});

		$grid->addColumn('received','todays_received');

		$grid->addMethod('format_paid',function($grid,$field){
			$grid->current_row[$field] = round($grid->model->allDrCrSum('Cr'),2);
		});

		$grid->addColumn('paid','todays_payment');


		$grid->addMethod('format_crbal',function($grid,$field){
			$bal = $grid->model->getOpeningBalance($grid->api->nextDate($grid->api->today));
			$bal_side = (($bal['Cr'] - $bal['Dr']) > 0) ? 'Cr' : 'Dr';
			$bal_fig = abs($bal['Cr'] - $bal['Dr']);
			$grid->current_row[$field] = round($bal_fig,2) . ' ' . $bal_side;
		});

		$grid->addColumn('crbal','balance_on_date');

		$this->add('H2')->set(array('Cash Report','icon'=>'flag'));
		$grid2=$this->add('Grid_AccountsBase');
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
			$grid->current_row[$field] = round($grid->model->allDrCrSum('Dr'),2);
		});

		$grid2->addColumn('received','Debit');

		$grid2->addMethod('format_paid',function($grid,$field){
			$grid->current_row[$field] = round($grid->model->allDrCrSum('Cr'),2);
		});

		$grid2->addColumn('paid','Credit');


		$grid2->addMethod('format_crbal',function($grid,$field){
			$bal = $grid->model->getOpeningBalance($grid->api->nextDate($grid->api->today));
			$bal_side = (($bal['Cr'] - $bal['Dr']) > 0) ? 'Cr' : 'Dr';
			$bal_fig = abs($bal['Cr'] - $bal['Dr']);
			$grid->current_row[$field] = round($bal_fig,2) . ' ' . $bal_side;
		});

		$grid2->addColumn('crbal','balance_on_date');

		
	}
}