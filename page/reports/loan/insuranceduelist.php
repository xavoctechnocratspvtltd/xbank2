<?php
class page_reports_loan_insuranceduelist extends Page {
	public $title="Insurance Due List";
	function init(){
		parent::init();


		$form=$this->add('Form');
		$dealer_field=$form->addField('dropdown','dealer')->setEmptyText('Please Select');
		$dealer_field->setModel('ActiveDealer');

		$form->addField('DatePicker','from_date');
		$form->addField('DatePicker','to_date');
		$document=$this->add('Model_Document');
		$document->addCondition('LoanAccount',true);
		foreach ($document as $junk) {
			$form->addField('CheckBox','doc_'.$document->id,$document['name']);
		}
		$form->addSubmit('GET List');

		$grid=$this->add('Grid'); 

		$accounts_model=$this->add('Model_Account_Loan');

		if($_GET['filter']){

			//TODO

		}

		$grid->setModel($accounts_model);

		$grid->addPaginator(50);


		if($form->isSubmitted()){
			$send = array('dealer'=>$form['dealer'],'from_date'=>$form['from_date']?:0,'to_date'=>$form['to_date']?:0,'filter'=>1);
			foreach ($document as $junk) {
				if($form['doc_'.$document->id])
					$send['doc_'.$document->id] = $form['doc_'.$document->id];
			}
			$grid->js()->reload($send)->execute();

		}	

	}
}