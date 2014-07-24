<?php

class page_reports_loan_dispatch extends Page {
	public $title="EMI Due List (Recovery List)";
	function init(){
		parent::init();

		$form=$this->add('Form');
		$dealer_field=$form->addField('dropdown','dealer')->setEmptyText('Please Select');
		$dealer_field->setModel('ActiveDealer');

		$form->addField('DatePicker','from_date');
		$form->addField('DatePicker','to_date');
		$form->addSubmit('GET List');

		$grid=$this->add('Grid'); 

		$account_model=$this->add('Model_Account_Loan');

		if($_GET['filter']){

			//TODO

		}

		$grid->setModel($account_model);

		$grid->addPaginator(50);


		if($form->isSubmitted()){

			$send = array('dealer'=>$form['dealer'],'from_date'=>$form['from_date']?:0,'to_date'=>$form['to_date']?:0,'filter'=>1);
			$grid->js()->reload($send)->execute();

		}		


	}
}