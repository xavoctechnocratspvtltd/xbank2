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
		$document_field=$form->addField('dropdown','document')->setEmptyText('All Documents');
		$document_field->setModel($document);
		$form->addField('line','value');

		$form->addSubmit('GET List');
		$grid=$this->add('Grid');

		$document_submitted_model=$this->add('Model_DocumentSubmitted');
		$document_join = $document_submitted_model->join('documents','documents_id');
		$account_join = $document_submitted_model->join('accounts','accounts_id');
		$dealer_join = $account_join->hasOne('Dealer','dealer_id');

		$account_join->addField('branch_id');
		// $account_join->addField('dealer_id');


		if($_GET['filter']){
			$this->api->stickyGET("filter");

			if($_GET['dealer']){
				$this->api->stickyGET("dealer");
				$document_submitted_model->addCondition('dealer_id',$_GET['dealer']);
			}

			if($_GET['from_date']){
				$this->api->stickyGET("from_date");
				$document_submitted_model->addCondition('submitted_on','>',$_GET['from_date']);
			}

			if($_GET['to_date']){
				$this->api->stickyGET("to_date");
				$document_submitted_model->addCondition('submitted_on','<',$this->api->nextDate($_GET['to_date']));
			}

			if($_GET['value']){
				$this->api->stickyGET("value");
				$document_submitted_model->addCondition('Description','like','%'.$_GET['value'].'%');
			}

			if($_GET['document']){
				$this->api->stickyGET("document");
				$document_submitted_model->addCondition('documents_id',$_GET['document']);
			}



		}

		$document_submitted_model->setOrder('accounts');
		$document_submitted_model->add('Controller_Acl');

		$grid->setModel($document_submitted_model);
		$grid->addPaginator(50);

		if($form->isSubmitted()){
			$send = array('dealer'=>$form['dealer'],'from_date'=>$form['from_date']?:0,'to_date'=>$form['to_date']?:0,'document'=>$form['document'],'value'=>$form['value'],'filter'=>1);
			$grid->js()->reload($send)->execute();

		}	


	}
}