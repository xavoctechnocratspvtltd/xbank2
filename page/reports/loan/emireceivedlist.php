<?php

class page_reports_loan_emireceivedlist extends Page {
	public $title="EMI Received List";
	function init(){
		parent::init();

		$form=$this->add('Form');
		$dealer_field=$form->addField('dropdown','dealer')->setEmptyText('Please Select');
		$dealer_field->setModel('ActiveDealer');

		$form->addField('DatePicker','from_date');
		$form->addField('DatePicker','to_date');

		$form->addField('dropdown','loan_type')->setValueList(array('vl'=>'VL','pl'=>'PL','other'=>'Other','all'=>'All'));
		$document=$this->add('Model_Document');
		$form->addSubmit('GET List');

		$grid=$this->add('Grid'); 

		$transaction_row_model=$this->add('Model_TransactionRow');

		if($_GET['filter']){

			//TODO

		}

		$grid->setModel($transaction_row_model);

		$grid->addPaginator(50);


		if($form->isSubmitted()){

			$grid->js()->reload(array('dealer'=>$form['dealer'],'from_date'=>$form['from_date']?:0,'to_date'=>$form['to_date']?:0,'loan_type'=>$form['loan_type'],'filter'=>1))->execute();

		}		


	}
}