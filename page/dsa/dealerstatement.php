<?php

class page_dsa_dealerstatement extends Page {
	public $title="Dealer Statement Repots";
	function page_index(){
		// parent::init();

		$from_date='1970-02-01';
		$to_date=$this->api->today;

		if($_GET['to_date']){
			$to_date = $this->api->stickyGET('to_date');
		}

		if($_GET['from_date']){
			$from_date = $this->api->stickyGET('from_date');
		}

		$dealer_model=$this->add('Model_Dealer');
		$dealer_model->addCondition('dsa_id',$this->api->auth->model->id);

		$form=$this->add('Form');
		$dealer=$form->addField('autocomplete/Basic','dealer')->validateNotNull();
		$dealer->setModel($dealer_model);
		$form->addField('DatePicker','from_date');
		$form->addField('DatePicker','to_date');
		$loan_type=$form->addField('DropDown','loan_type');
		$array_value = $array_key = explode(',',LOAN_TYPES);
		$loan_type->setValueList(array_combine($array_key, $array_value))->setEmptyText('Select Account type');

		$form->addField('DropDown','status')->setValueList(array(1=>'Active',0=>'Inactive'));
		$document=$this->add('Model_Document');
		$document->addCondition('LoanAccount',true);
		foreach ($document as $junk) {
			$form->addField('CheckBox','doc_'.$document->id, $document['name']);
		}

		$form->addSubmit('GET List');

		$grid=$this->add('Grid_Report_DealerStatement',array('from_date'=>$from_date,'to_date'=>$to_date,'account_type'=>$_GET['account_type'],'account_status'=>$_GET['status']));
		$grid->add('H3',null,'grid_buttons')->set('Dealer Payment Detail From Date - '.$from_date.' To Date - '.$to_date);

		// $account_model->add('Controller_Acl');
		// $grid->addColumn('loan_amount');
		// $grid->addColumn('net_amount');
		// $grid->addColumn('file_charge');
		// $grid->addColumn('bank_detail');
		
		$account_model = $this->add('Model_Account');
		$dealer_id = -1;
		if($_GET['dealer']){
			$dealer_id = $this->api->stickyGET('dealer');
		}

		if($_GET['loan_type']){
			$account_type_value = $this->api->stickyGET('loan_type');
		}

		$account_model->addCondition('dealer_id',$dealer_id);

		$grid_column_array = array('AccountNumber','scheme','name','FatherName','CurrentAddress','PhoneNos','dealer_id','ActiveStatus');
		if($_GET['filter']){
			$account_model->addCondition('created_at','>=',$from_date);
			$account_model->addCondition('created_at','<=',$this->api->nextDate($to_date));
			
			if(isset($account_type_value)){
				$account_model->addCondition('SchemeType','Loan');
				$account_model->addCondition('account_type',$account_type_value);
			}

			$status = 1;
			if($_GET['status'] == 0){
				$account_model->addCondition('ActiveStatus','<>',1);
			 }else{
				$status = $this->api->stickyGET('status');
				$account_model->addCondition('ActiveStatus',$status);
			 }


			$member_join = $account_model->leftJoin('members','member_id');
			$member_join->addField('FatherName')->caption('Father/Husband Name');
			$member_join->addField('CurrentAddress');
			$member_join->addField('PhoneNos');

			foreach ($document as $junk) {
				$doc_id = $document->id;
				if($_GET['doc_'.$document->id]){
					$this->api->stickyGET('doc_'.$document->id);
					$account_model->addExpression($this->api->normalizeName($document['name']))->set(function($m,$q)use($doc_id ){
						return $m->refSQL('DocumentSubmitted')->addCondition('documents_id',$doc_id )->fieldQuery('Description');
					});
					$grid_column_array[] = $this->api->normalizeName($document['name']);
				}
			}
		}
		

		$grid->setModel($account_model,$grid_column_array);

		// $move fileds at and 

		if($form->isSubmitted()){
			$send = array('dealer'=>$form['dealer'],'status'=>$form['status'],'to_date'=>$form['to_date']?:0,'from_date'=>$form['from_date']?:0,'loan_type'=>$form['loan_type'],'filter'=>1);
			foreach ($document as $junk) {
				if($form['doc_'.$document->id])
					$send['doc_'.$document->id] = $form['doc_'.$document->id];
			}
			$grid->js()->reload($send)->execute();
		}

	}

}
