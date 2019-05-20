<?php

class page_reports_member_defaulter extends Page{
	function init(){
		parent::init();
		
		$this->api->stickyGET('filter');
		$this->api->stickyGET('from_date');
		$this->api->stickyGET('to_date');

		$member_model=$this->add('Model_Member');
		$member_model->setOrder('created_at','desc');
		$member_model->addCondition('is_defaulter',true);
		$form=$this->add('Form');
		$form->addField('DatePicker','from_date');
		$form->addField('DatePicker','to_date');

		$grid=$this->add('Grid_AccountsBase');
		$grid_column_array = array('member_no','branch','name','FatherName','AdharNumber','CurrentAddress','tehsil','city','PhoneNos','created_at','is_active','defaulter_on');
		// $grid->add('H3',null,'grid_buttons')->set('Member Repo As On '. date('d-M-Y',strtotime($till_date))); 

		$document=$this->add('Model_Document');
		$document->addCondition('MemberDocuments',true);
		foreach ($document as $junk) {
			$form->addField('CheckBox','doc_'.$document->id, $document['name']);
		}
		$form->addSubmit('GET List');

		if($_GET['filter']){

			if($_GET['from_date']){
				$member_model->addCondition('defaulter_on','>',$_GET['from_date']);
			}

			if($_GET['to_date']){
				$member_model->addCondition('defaulter_on','<=',$this->app->nextDate($_GET['to_date']));
			}

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
		$grid->addQuickSearch(array('member_no','name','PhoneNos'));

		// $grid->add('Controller_DocumentsManager',array('doc_type'=>'MemberDocuments'));

		if($form->isSubmitted()){

			$send = array('dealer'=>$form['dealer'],'till_date'=>$form['till_date']?:0,'report_type'=>$form['report_type'], 'loan_type'=>$form['loan_type'],'from_date'=>$form['from_date']?:0,'to_date'=>$form['to_date']?:0,'filter'=>1);
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