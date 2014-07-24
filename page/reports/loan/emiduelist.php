<?php

class page_reports_loan_emiduelist extends Page {
	public $title="EMI Due List (Recovery List)";
	function init(){
		parent::init();

		$form=$this->add('Form');
		$dealer_field=$form->addField('dropdown','dealer')->setEmptyText('Please Select');
		$dealer_field->setModel('ActiveDealer');

		$form->addField('DatePicker','from_date');
		$form->addField('DatePicker','to_date');
		$form->addField('dropdown','report_type')->setValueList(array('duelist'=>'Due List','hardlist'=>'Hard List','npa'=>'NPA List','time_collapse'=>'Time Collaps'));
		$form->addField('dropdown','loan_type')->setValueList(array('vl'=>'VL','pl'=>'PL','other'=>'Other','all'=>'All'));
		$document=$this->add('Model_Document');
		$document->addCondition('LoanAccount',true);
		foreach ($document as $junk) {
		$form->addField('CheckBox',$document['name']);
		}
		$form->addSubmit('GET List');

		$grid=$this->add('Grid'); 

		$preminum_model=$this->add('Model_Premium');

		if($_GET['filter']){

			//TODO

		}

		$grid->setModel($preminum_model);

		$grid->addPaginator(50);


		if($form->isSubmitted()){

			$send = array('dealer'=>$form['dealer'],'from_date'=>$form['from_date']?:0,'to_date'=>$form['to_date']?:0,'loan_type'=>$form['loan'],'filter'=>1);
			foreach ($document as $junk) {
				if($form['doc_'.$document->id])
					$send['doc_'.$document->id] = $form['doc_'.$document->id];
			}
			$grid->js()->reload($send)->execute();

		}		


	}
}