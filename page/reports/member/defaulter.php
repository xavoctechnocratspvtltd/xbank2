<?php

class page_reports_member_defaulter extends Page{
	function init(){
		parent::init();

		$member_model=$this->add('Model_Member');
		$member_model->setOrder('created_at','desc');
		$member_model->addCondition('is_defaulter',true);
		$form=$this->add('Form');
		$grid=$this->add('Grid_AccountsBase');
		$grid_column_array = array('id','branch','name','CurrentAddress','tehsil','city','PhoneNos','created_at','is_active');
		// $grid->add('H3',null,'grid_buttons')->set('Member Repo As On '. date('d-M-Y',strtotime($till_date))); 

		$document=$this->add('Model_Document');
		$document->addCondition('MemberDocuments',true);
		foreach ($document as $junk) {
			$form->addField('CheckBox','doc_'.$document->id, $document['name']);
		}
		$form->addSubmit('GET List');

		if($_GET['filter']){
			$this->api->stickyGET('filter');
			foreach ($document as $junk) {
				$doc_id = $document->id;
				if($_GET['doc_'.$document->id]){
					$this->api->stickyGET('doc_'.$document->id);
					$member_model->addExpression($this->api->normalizeName($document['name']))->set(function($m,$q)use($doc_id ){
						return $m->refSQL('DocumentSubmitted')->addCondition('documents_id',$doc_id )->fieldQuery('Description');
					});
					$grid_column_array[] = $this->api->normalizeName($document['name']);
				}
			}

		}else
			$member_model->addCondition('id',-1);




		$grid->setModel($member_model,$grid_column_array);
		$grid->addSno();
		$grid->addPaginator(500);
		$grid->addQuickSearch(array('id','name','PhoneNos'));

		// $grid->add('Controller_DocumentsManager',array('doc_type'=>'MemberDocuments'));

		if($form->isSubmitted()){

			$send = array('dealer'=>$form['dealer'],'till_date'=>$form['till_date']?:0,'report_type'=>$form['report_type'], 'loan_type'=>$form['loan_type'],'filter'=>1);
			foreach ($document as $junk) {
				if($form['doc_'.$document->id])
					$send['doc_'.$document->id] = $form['doc_'.$document->id];
			}
			$grid->js()->reload($send)->execute();

		}

		// $grid->addColumn('expander','details');
		// $grid->addColumn('expander','accounts');
		// $grid->addColumn('expander','guarantor_in');
	}
}