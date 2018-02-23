<?php

class View_DuesGiveList extends View{
	
	public $from_date;
	public $to_date;
	
	function init(){
		parent::init();

		$heading = $this->add('H2')->set(array('Dues To Give List','icon'=>'flag'));
		
		// === DDS VIEW
		$dds_view = $this->add('View');
		$dds_heading = $dds_view->add('H4')->set('DDS Accounts');

		$dds_maturity_accounts = $dds_view->add('Model_Active_Account_DDS');
		$dds_maturity_accounts->addCondition('ActiveStatus',true);
		$dds_maturity_accounts->_dsql()->having('maturity_date','>=',$this->from_date);
		$dds_maturity_accounts->_dsql()->having('maturity_date','<',$this->to_date);
		$dds_maturity_accounts->addCondition('DefaultAC',false);

		$dds_maturity_accounts->addExpression('MaturityAmount','(CurrentBalanceCr + CurrentInterest - CurrentBalanceDr)');

		$dds_member_j = $dds_maturity_accounts->join('members','member_id');
		$dds_member_j->addField('member_name','name');
		$dds_member_j->addField('FatherName');
		$dds_member_j->addField('PermanentAddress');
		$dds_member_j->addField('PhoneNos');
		
		$dds_maturity_accounts->add('Controller_Acl');

		$dds_grid = $dds_view->add('Grid_AccountsBase');
		$dds_grid->setModel($dds_maturity_accounts,array('AccountNumber','member_name','FatherName','PermanentAddress','PhoneNos', 'MaturityAmount','maturity_date','agent'));
		$dds_grid->addSno();
		
		$dds_grid->addTotals(array('MaturityAmount'));
		$dds_heading->js('click',$dds_grid->js()->toggle());
		$dds_grid->setFormatter('AccountNumber','template')->setTemplate('<a href="#" class="acclink" data-id="{$id}">{AccountNumber}dummy{/}</a>','AccountNumber');
		// Recurring View
		
		$recurring_view = $this->add('View');
		$recurring_heading = $recurring_view->add('H4')->set('Recurring View');

		$recurring_maturity_accounts = $recurring_view->add('Model_Active_Account_Recurring');
		$recurring_maturity_accounts->addCondition('ActiveStatus',true);
		$recurring_maturity_accounts->_dsql()->having('maturity_date','>=',$this->from_date);
		$recurring_maturity_accounts->_dsql()->having('maturity_date','<',$this->to_date);
		$recurring_maturity_accounts->addCondition('DefaultAC',false);

		$recurring_maturity_accounts->addExpression('MaturityAmount','(CurrentBalanceCr + CurrentInterest - CurrentBalanceDr)');

		$recurring_member_j = $recurring_maturity_accounts->join('members','member_id');
		$recurring_member_j->addField('member_name','name');
		$recurring_member_j->addField('FatherName');
		$recurring_member_j->addField('PermanentAddress');
		$recurring_member_j->addField('PhoneNos');

		
		$recurring_maturity_accounts->add('Controller_Acl');

		$recurring_grid = $recurring_view->add('Grid_AccountsBase');
		$recurring_grid->setModel($recurring_maturity_accounts,array('AccountNumber','member_name','FatherName','PermanentAddress','PhoneNos', 'MaturityAmount','maturity_date','agent'));
		$recurring_grid->addSno();

		$recurring_grid->addTotals(array('MaturityAmount'));

		$recurring_heading->js('click',$recurring_grid->js()->toggle());
		$recurring_grid->setFormatter('AccountNumber','template')->setTemplate('<a href="#" class="acclink" data-id="{$id}">{AccountNumber}dummy{/}</a>','AccountNumber');
		
		// FixedAndMis 
		
		$fd_mis_view = $this->add('View');
		$fd_mis_heading = $fd_mis_view->add('H4')->set('FD & MIS View');

		$fd_mis_maturity_accounts = $fd_mis_view->add('Model_Active_Account_FixedAndMis');
		$fd_mis_maturity_accounts->addCondition('ActiveStatus',true);
		$fd_mis_maturity_accounts->_dsql()->having('maturity_date','>=',$this->from_date);
		$fd_mis_maturity_accounts->_dsql()->having('maturity_date','<',$this->to_date);
		$fd_mis_maturity_accounts->addCondition('DefaultAC',false);

		$fd_mis_maturity_accounts->addExpression('MaturityAmount','(CurrentBalanceCr + CurrentInterest - CurrentBalanceDr)');

		$fd_mis_member_j = $fd_mis_maturity_accounts->join('members','member_id');
		$fd_mis_member_j->addField('member_name','name');
		$fd_mis_member_j->addField('FatherName');
		$fd_mis_member_j->addField('PermanentAddress');
		$fd_mis_member_j->addField('PhoneNos');
		
		$fd_mis_maturity_accounts->add('Controller_Acl');

		$fd_mis_grid = $fd_mis_view->add('Grid_AccountsBase');
		$fd_mis_grid->setModel($fd_mis_maturity_accounts,array('AccountNumber','member_name','FatherName','PermanentAddress','PhoneNos', 'MaturityAmount','maturity_date','agent'));
		$fd_mis_grid->addSno();
		$fd_mis_grid->addTotals(array('MaturityAmount'));
		$fd_mis_heading->js('click',$fd_mis_grid->js()->toggle());

		$fd_mis_grid->setFormatter('AccountNumber','template')->setTemplate('<a href="#" class="acclink" data-id="{$id}">{AccountNumber}dummy{/}</a>','AccountNumber');
	}
}