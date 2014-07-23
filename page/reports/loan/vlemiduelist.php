<?php

class page_reports_loan_vlemiduelist extends Page {
	public $title="VL EMI Due List";
	function init(){
		parent::init();

		$form=$this->add('Form');
		$dealer_field=$form->addField('dropdown','dealer');
		$dealer_field->setModel('ActiveDealer');

		$form->addField('DatePicker','from_date');
		$form->addField('DatePicker','to_date');
		$form->addSubmit('GET List');

		$grid=$this->add('Grid');

		$preminum_model=$this->add('Model_Premium');

		if($_GET['filter']){

			//TODO

		}

		$grid->setModel($preminum_model);

		$grid->addPaginator(50);


		if($form->isSubmitted()){

			$grid->js()->reload(array('dealer'=>$form['dealer'],'from_date'=>$form['from_date'],'to_date'=>$form['to_date'],'filter'=>1))->execute();

		}		


	}
}