<?php

class page_dashboard_cash extends Page {
	function init(){
		parent::init();

		$heading = $this->add('H2')->set(array('Bank Report','icon'=>'flag'));

		$grid=$this->add('Grid_AccountsBase');
		$accounts=$this->add('Model_Account');
		$accounts->addCondition('scheme_name',BANK_ACCOUNTS_SCHEME);
		$accounts->add('Controller_Acl');
		$grid->setModel($accounts,array('AccountNumber'));
		$grid->addSno();

		$grid->addMethod('format_crbal',function($grid,$field){
			$bal = $grid->model->getOpeningBalance($grid->api->today);
			$bal_side = $bal['Cr'] - $bal['Dr'] > 0 ? 'Cr' : 'Dr';
			$bal_fig = abs($bal['Cr'] - $bal['Dr']);
			$grid->current_row[$field] = $bal_fig . ' ' . $bal_side;
		});

		$grid->addColumn('crbal','balance_on_date');


		$this->add('H2')->set(array('Cash Report','icon'=>'flag'));
		$grid2=$this->add('Grid_AccountsBase');
		$account_cash=$this->add('Model_Account');
		$account_cash->addCondition('scheme_name',CASH_ACCOUNT);
		$account_cash->add('Controller_Acl');
		$grid2->setModel($account_cash,array('AccountNumber'));
		$grid2->addSno();

		$grid2->addMethod('format_crbal',function($grid,$field){
			$bal = $grid->model->getOpeningBalance($grid->api->today);
			$bal_side = $bal['Cr'] - $bal['Dr'] > 0 ? 'Cr' : 'Dr';
			$bal_fig = abs($bal['Cr'] - $bal['Dr']);
			$grid->current_row[$field] = $bal_fig . ' ' . $bal_side;
		});

		$grid2->addColumn('crbal','balance_on_date');
	}
}