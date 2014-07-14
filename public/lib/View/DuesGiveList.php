<?php

class View_DuesGiveList extends View{
	function init(){
		parent::init();

		$heading = $this->add('H2')->set(array('Dues To Give List','icon'=>'flag'));
		$container = $this->add('View');

		$this->js(true,$container->js()->hide());
		$heading->js('click',$container->js()->toggle());


		$from_date = $this->api->today;
		$to_date = $this->api->nextDate($this->api->today);
		if($_GET['from_date'])
			$from_date = $_GET['from_date'];
		if($_GET['to_date'])
			$to_date = $this->api->nextDate($_GET['to_date']);

		$form=$container->add('Form');
		$form->addField('DatePicker','from_date')->set($from_date);
		$form->addField('DatePicker','to_date')->set($to_date);
		$form->addSubmit('Get List');

		// === DDS VIEW
		$dds_view = $container->add('View');
		$dds_heading = $dds_view->add('H4')->set('DDS Accounts');

		$dds_maturity_accounts = $dds_view->add('Model_Active_Account_DDS');
		$dds_maturity_accounts->addCondition('ActiveStatus',true);
		$dds_maturity_accounts->addCondition('maturity_date','>=',$from_date);
		$dds_maturity_accounts->addCondition('maturity_date','<',$to_date);
		$dds_maturity_accounts->addCondition('DefaultAC',false);

		$dds_maturity_accounts->addExpression('MaturityAmount','(CurrentBalanceCr + CurrentInterest - CurrentBalanceDr)');

		
		$dds_maturity_accounts->add('Controller_Acl');

		$dds_grid = $dds_view->add('Grid');
		$dds_grid->setModel($dds_maturity_accounts,array('AccountNumber','maturity_date','MaturityAmount'));

		$dds_grid->addTotals(array('MaturityAmount'));
		$dds_heading->js('click',$dds_grid->js()->toggle());
		// Recurring View
		
		$recurring_view = $container->add('View');
		$recurring_heading = $recurring_view->add('H4')->set('Recurring View');

		$recurring_maturity_accounts = $recurring_view->add('Model_Active_Account_Recurring');
		$recurring_maturity_accounts->addCondition('ActiveStatus',true);
		$recurring_maturity_accounts->addCondition('maturity_date','>=',$from_date);
		$recurring_maturity_accounts->addCondition('maturity_date','<',$to_date);
		$recurring_maturity_accounts->addCondition('DefaultAC',false);

		$recurring_maturity_accounts->addExpression('MaturityAmount','(CurrentBalanceCr + CurrentInterest - CurrentBalanceDr)');

		
		$recurring_maturity_accounts->add('Controller_Acl');

		$recurring_grid = $recurring_view->add('Grid');
		$recurring_grid->setModel($recurring_maturity_accounts,array('AccountNumber','maturity_date','MaturityAmount'));

		$recurring_grid->addTotals(array('MaturityAmount'));

		$recurring_heading->js('click',$recurring_grid->js()->toggle());

		// FixedAndMis 
		
		$fd_mis_view = $container->add('View');
		$fd_mis_heading = $fd_mis_view->add('H4')->set('FD & MIS View');

		$fd_mis_maturity_accounts = $fd_mis_view->add('Model_Active_Account_FixedAndMis');
		$fd_mis_maturity_accounts->addCondition('ActiveStatus',true);
		$fd_mis_maturity_accounts->addCondition('maturity_date','>=',$from_date);
		$fd_mis_maturity_accounts->addCondition('maturity_date','<',$to_date);
		$fd_mis_maturity_accounts->addCondition('DefaultAC',false);

		$fd_mis_maturity_accounts->addExpression('MaturityAmount','(CurrentBalanceCr + CurrentInterest - CurrentBalanceDr)');

		
		$fd_mis_maturity_accounts->add('Controller_Acl');

		$fd_mis_grid = $fd_mis_view->add('Grid');
		$fd_mis_grid->setModel($fd_mis_maturity_accounts,array('AccountNumber','maturity_date','MaturityAmount'));

		$fd_mis_grid->addTotals(array('MaturityAmount'));
		
		$fd_mis_heading->js('click',$fd_mis_grid->js()->toggle());

		// Form Submit
		if($form->isSubmitted()){
			$container->js()->reload(array(
					'from_date'=>$form['from_date']?:0,
					'to_date'=>$form['to_date']?:0,
				))->execute();
		}

	}
}