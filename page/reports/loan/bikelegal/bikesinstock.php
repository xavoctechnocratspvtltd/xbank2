<?php

class page_reports_loan_bikelegal_bikesinstock extends Page {
	public $title="Bike In Stock Report";
	
	function init(){
		parent::init();

		$form= $this->add('Form');
		$dealer_field=$form->addField('dropdown','dealer')->setEmptyText('All');
		$dealer_field->setModel('ActiveDealer');

		$document=$this->add('Model_Document');
		$document->addCondition('LoanAccount',true);
		foreach ($document as $junk) {
			$form->addField('CheckBox','doc_'.$document->id, $document['name']);
		}

		$form->addSubmit('Get List');

		$account_model = $this->add('Model_Account_Loan');
		$account_model->addCondition('DefaultAC',false);

		$member_j = $account_model->join('members','member_id');
		$member_j->addField('FatherName');
		$member_j->addField('PermanentAddress');
		$member_j->addField('PhoneNos');

		$grid_column_array = ['AccountNumber','member','FatherName','PermanentAddress','landmark','tehsil','district','PhoneNos','dealer','bike_surrendered_by','bike_surrendered_on'];

		if($this->api->stickyGET('filter')){
			if($this->api->stickyGET('dealer')){
				$account_model->addCondition('dealer_id',$_GET['dealer']);
			}

			foreach ($document as $junk) {
				$doc_id = $document->id;
				if($this->api->stickyGET('doc_'.$document->id)){
					$this->api->stickyGET('doc_'.$document->id);
					$account_model->addExpression($this->api->normalizeName($document['name']))->set(function($m,$q)use($doc_id ){
						return $m->refSQL('DocumentSubmitted')->addCondition('documents_id',$doc_id )->fieldQuery('Description');
					});
					$grid_column_array[] = $this->api->normalizeName($document['name']);
				}
			}
		}


		$grid = $this->add('Grid');

		$grid->setModel($account_model,$grid_column_array);
		$grid->addPaginator(100);

		if($form->isSubmitted()){
			$send = array('filter'=>1,'dealer'=>$form['dealer']);
			foreach ($document as $junk) {
				if($form['doc_'.$document->id])
					$send['doc_'.$document->id] = $form['doc_'.$document->id];
			}
			$grid->js()->reload($send)->execute();
		}

	}
}