<?php

class page_reports_general_document extends Page {
	public $title='Document Report';

	function init(){
		parent::init();

		$form=$this->add('Form');
		$dealer_field=$form->addField('dropdown','dealer')->setEmptyText('Please Select');
		$dealer_field->setModel('ActiveDealer');

		$form->addField('DatePicker','from_date');
		$form->addField('DatePicker','to_date');
		$document=$this->add('Model_Document');
		$document->addCondition('LoanAccount',true);
		$document_field=$form->addField('dropdown','document');
		$document_field->setModel($document);
		$form->addField('line','value');

		$form->addSubmit('GET List');


		$grid=$this->add('Grid');

		$document_submitted_model=$this->add('Model_DocumentSubmitted');

		$grid->setModel($document_submitted_model);
		$grid->addPaginator(50);

		if($form->isSubmitted()){
			$send = array('dealer'=>$form['dealer'],'from_date'=>$form['from_date']?:0,'to_date'=>$form['to_date']?:0,'value'=>$form['value'],'filter'=>1);
			$grid->js()->reload($send)->execute();

		}	


	}
}