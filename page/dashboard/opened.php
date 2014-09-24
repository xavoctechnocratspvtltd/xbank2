<?php

class page_dashboard_opened extends Page {
	function init(){
		parent::init();

		$heading = $this->add('H2')->set(array('Account Open Today List','icon'=>'flag'));

		$grid=$this->add('Grid_AccountsBase');
		$accounts=$this->add('Model_Account');
		$accounts->addCondition('created_at','>=',$this->api->today);
		$accounts->addCondition('created_at','<',$this->api->nextDate($this->api->today));

		$member_j = $accounts->join('members','member_id');

		$member_j->addField('member_name','name');
		$member_j->addField('FatherName');
		$member_j->addField('PermanentAddress');
		$member_j->addField('PhoneNos');

		$accounts->add('Controller_Acl');

		$grid->setModel($accounts,array('AccountNumber','member_name','FatherName','PermanentAddress','PhoneNos','scheme', 'Amount','DueDate','agent','dealer'));

		$grid->addMethod('format_crbal',function($grid,$field){
			$bal = $grid->model->getOpeningBalance($grid->api->nextDate($grid->api->today));
			$bal_side = $bal['Cr'] - $bal['Dr'] > 0 ? 'Cr' : 'Dr';
			$bal_fig = abs($bal['Cr'] - $bal['Dr']);
			$grid->current_row[$field] = $bal_fig . ' ' . $bal_side;
		});

		$grid->addColumn('crbal','balance_on_date');

		$grid->addSno();
	}
}