<?php

class View_DuesReceiveList extends View{
		public $from_date=null;
		public $to_date=null;

	function init(){
		parent::init();

		if($this->from_date == null ) $this->from_date = $this->api->today	;
		if($this->to_date == null ) $this->to_date = $this->api->nextDate($this->api->today	);

		$heading = $this->add('H2')->set(array('Dues To Received List','icon'=>'flag'));
		$due_premiums = $this->add('Model_Premium');
		$account_j=$due_premiums->join('accounts','account_id');
		$member_j = $account_j->join('members','member_id');

		$account_j->addField('DefaultAc');
		$account_j->addField('AccountNumber');
		$account_j->addField('ActiveStatus');
		$account_j->addField('branch_id');
		$account_j->hasOne('Agent','agent_id');
		$account_j->hasOne('Agent','dealer_id');

		$scheme_j = $account_j->join('schemes','scheme_id');
		$scheme_j->addField('SchemeType');

		$member_j->addField('member_name','name');
		$member_j->addField('FatherName');
		$member_j->addField('PermanentAddress');
		$member_j->addField('PhoneNos');

		$due_premiums->setOrder('SchemeType,AccountNumber');

		$due_premiums->addCondition('DueDate','>=',$this->from_date);
		$due_premiums->addCondition('DueDate','<',$this->to_date);
		$due_premiums->addCondition('Paid',0);
		$due_premiums->addCondition('ActiveStatus',true);

		$due_premiums->add('Controller_Acl');

		$grid = $this->add('Grid_AccountsBase');
		$grid->setModel($due_premiums,array('AccountNumber','member_name','FatherName','PermanentAddress','PhoneNos', 'Amount','DueDate','agent','dealer'));
		$grid->addSno();

		$grid->addTotals(array('Amount'));


	}
}