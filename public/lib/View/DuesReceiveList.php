<?php

class View_DuesReceiveList extends View{
	function init(){
		parent::init();

		$heading = $this->add('H2')->set(array('Dues Receive List','icon'=>'flag'));
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

		$due_premiums = $container->add('Model_Premium');
		$account_j=$due_premiums->join('accounts','account_id');
		$account_j->addField('DefaultAc');
		$account_j->addField('ActiveStatus');
		$account_j->addField('branch_id');

		$due_premiums->addCondition('DueDate','>=',$from_date);
		$due_premiums->addCondition('DueDate','<',$to_date);
		$due_premiums->addCondition('Paid',false);

		$due_premiums->add('Controller_Acl');

		$grid = $container->add('Grid');
		$grid->setModel($due_premiums,array('account','Amount','DueDate'));

		$grid->addTotals(array('Amount'));

		if($form->isSubmitted()){
			$grid->js()->reload(array(
					'from_date'=>$form['from_date']?:0,
					'to_date'=>$form['to_date']?:0,
				))->execute();
		}

	}
}