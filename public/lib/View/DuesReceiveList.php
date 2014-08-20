<?php

class View_DuesReceiveList extends View{
		public $from_date;
		public $to_date;
	function init(){
		parent::init();

		$heading = $this->add('H2')->set(array('Dues To Received List','icon'=>'flag'));
		$due_premiums = $this->add('Model_Premium');
		$account_j=$due_premiums->join('accounts','account_id');
		$account_j->addField('DefaultAc');
		$account_j->addField('ActiveStatus');
		$account_j->addField('branch_id');

		$due_premiums->addCondition('DueDate','>=',$this->from_date);
		$due_premiums->addCondition('DueDate','<',$this->to_date);
		$due_premiums->addCondition('Paid',false);

		$due_premiums->add('Controller_Acl');

		$grid = $this->add('Grid');
		$grid->setModel($due_premiums,array('account','Amount','DueDate'));

		$grid->addTotals(array('Amount'));


	}
}