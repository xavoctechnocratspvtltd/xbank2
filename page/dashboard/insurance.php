<?php

class page_dashboard_insurance extends Page {
	function init(){
		parent::init();

		$heading = $this->add('H2')->set(array('Insurance Due Report','icon'=>'flag'));

		$grid=$this->add('Grid_AccountsBase');
		
		$accounts=$this->add('Model_Active_Account_Loan',array('table_alias'=>'xa'));

		$member_j= $accounts->join('members','member_id');

		$member_j->addField('member_name','name');
		$member_j->addField('FatherName');
		$member_j->addField('PermanentAddress');
		$member_j->addField('PhoneNos');

		$accounts->addExpression('month')->set('MONTH(xa.LoanInsurranceDate)');
		// $accounts->addExpression('date')->set('DAY(xa.LoanInsurranceDate)');

		$accounts->add('Controller_Acl');
		$accounts->addCondition('month',date('m',strtotime($this->api->nextMonth($this->api->today))));
		$accounts->addCondition('LoanInsurranceDate','<=',$this->api->previousYear($this->api->nextMonth($this->api->nextMonth($this->api->today))));

		$grid->setModel($accounts,array('AccountNumber','member_name','FatherName','PhoneNos','LoanInsurranceDate','dealer','PermanentAddress'));
		$grid->addSno();

	}
}