<?php

class page_dashboard_insurance extends Page {
	function init(){
		parent::init();

		$heading = $this->add('H2')->set(array('Insurance Due Report','icon'=>'flag'));
		$grid=$this->add('Grid_AccountsBase');
		$accounts=$this->add('Model_Active_Account',array('table_alias'=>'xa'));

		$accounts->addExpression('month')->set('MONTH(xa.created_at)');

		$accounts->addExpression('date')->set('DAY(xa.created_at)');

		$accounts->add('Controller_Acl');
		$accounts->addCondition('month',date('m',strtotime($this->api->today)));
		$accounts->addCondition('date',date('d',strtotime($this->api->today)));
		$grid->setModel($accounts);
		$grid->addSno();

	}
}