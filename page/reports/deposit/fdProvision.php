<?php

class page_reports_deposit_fdProvision extends Page {
	public $title='FD Provision Report';

	function init(){
		parent::init();

		$from_date = $this->api->today;
		$to_date = $this->api->nextDate($this->api->today);
		if($_GET['from_date'])
			$from_date = $_GET['from_date'];
		if($_GET['to_date'])
			$to_date = $this->api->nextDate($_GET['to_date']);

		$form = $this->add('Form');
		$accounts_no_field=$form->addField('autocomplete/Basic','account_no');

		$accounts=$this->add('Model_Account');
		$accounts_no_field->setModel($accounts);

		$form->addField('DatePicker','from_date','From Month');
		$form->addField('DatePicker','to_date','To Month');
		$form->addSubmit('Go');

		$static_accounts = $this->add('Model_Account_FixedAndMis');
		$static_accounts->addCondition('ActiveStatus',true);
		$provisions= $static_accounts->provisions($from_date,$to_date);
		$grid = $this->add('Grid');
		$grid->setModel($provisions,array('related_account','amountCr','amountDr'));

		if($form->isSubmitted()){
			$grid->js()->reload(array(
					'from_date'=>$form['from_date']?:0,
					'to_date'=>$form['to_date']?:0,
				))->execute();
		}



	}
}