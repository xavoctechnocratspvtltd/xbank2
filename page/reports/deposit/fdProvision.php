<?php

class page_reports_deposit_fdProvision extends Page {
	public $title='FD Provision Report';

	function init(){
		parent::init();
		$till_date="";
		
		if($_GET['to_date']){
			$till_date=$_GET['to_date'];
		}

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

		$grid->add('H3',null,'grid_buttons')->set('FD Provision List As On '. date('d-M-Y',strtotime($till_date))); 
		$grid->setModel($provisions,array('related_account','amountCr','amountDr'));

		$js=array(
			$this->js()->_selector('.mymenu')->parent()->parent()->toggle(),
			$this->js()->_selector('#header')->toggle(),
			$this->js()->_selector('#footer')->toggle(),
			$this->js()->_selector('ul.ui-tabs-nav')->toggle(),
			$this->js()->_selector('.atk-form')->toggle(),
			);

		$grid->js('click',$js);
		if($form->isSubmitted()){
			$grid->js()->reload(array(
					'from_date'=>$form['from_date']?:0,
					'to_date'=>$form['to_date']?:0,
				))->execute();
		}



	}
}