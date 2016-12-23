<?php

class page_reports_loan_documentrenew extends Page {
	public $title="CC Document Renew Repots";
	function page_index(){
		// parent::init();

		$as_on_date=$this->api->today;
		
		if($_GET['as_on_date']){
			$as_on_date=$this->api->stickyGET('as_on_date');
		}

		$form=$this->add('Form');
		$account_no_field = $form->addField('autocomplete/Basic','account_no')->validateNotNull();
		$account_no_field->setModel('Account_CC');
		
		$form->addField('DatePicker','as_on_date');

		$form->addSubmit('GET List');

		$grid=$this->add('Grid_Report_CCDocumentRenew',array('as_on_date'=>$as_on_date));
		$grid->add('H3',null,'grid_buttons')->set('CC Over Due Report  As On '. date('d-M-Y',strtotime($as_on_date))); 

		$account_model = $this->add('Model_Account_CC');
		$selected_account_no = -1;
		if($_GET['filter']){
			if($_GET['account_no']){
				$selected_account_no = $this->api->stickyGET('account_no');
			}
		}

		$account_model->addCondition('id',$selected_account_no);
		$member_join = $account_model->leftJoin('members','member_id');
		$member_join->addField('FatherName')->caption('Father/Husband Name');
		$member_join->addField('CurrentAddress');
		$member_join->addField('PhoneNos');

		$grid->addSno();
		
		$grid->setModel($account_model,array('AccountNumber','name','FatherName','CurrentAddress','PhoneNos','Amount'));

		if($form->isSubmitted()){
			$grid->js()->reload(array('account_no'=>$form['account_no'],'as_on_date'=>$form['as_on_date']?:0,'filter'=>1))->execute();
		}	

	}

}
