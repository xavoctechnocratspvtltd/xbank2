<?php

class page_reports_deposit_fdinterestprovision extends Page {

	public $title="FD Interest Provision";
	function init(){
		parent::init();

		$form=$this->add('Form');
		$account_field=$form->addField('autocomplete/Basic','account_no');
		$account_field->setModel('Account');
		$form->addField('DatePicker','from_date');
		$form->addField('DatePicker','to_date');

		$form->addSubmit('GET List');

		$grid=$this->add('Grid');
		if($_GET['filter']){


		}

		$account=$this->add('Model_Account');
		$grid->setModel($account);
		$grid->addPaginator(50);


		if($form->isSubmitted()){
			$grid->js()->reload(array('account_no'=>$form['account_no'],'to_date'=>$form['to_date']?:0,'from_date'=>$form['from_date']?:0,'filter'=>1))->execute();
		}
	}
}