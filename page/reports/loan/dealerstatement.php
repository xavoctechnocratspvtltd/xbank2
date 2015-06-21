<?php

class page_reports_loan_dealerstatement extends Page {
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

		
		$form=$this->add('Form');
		$dealer=$form->addField('autocomplete/Basic','dealer')->validateNotNull();
		$dealer->setModel('Model_Dealer');
		$form->addField('DatePicker','from_date');
		$form->addField('DatePicker','to_date');
		$account_type=$form->addField('DropDown','account_type');
		$array_value = $array_key = explode(',', ACCOUNT_TYPES);
		$account_type->setValueList(array_combine($array_key, $array_value))->setEmptyText('Select Account type');

		$form->addField('DropDown','status')->setValueList(array(1=>'Active',0=>'Inactive'));

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

		if($_GET['account_type']){
			$account_type_value = $this->api->stickyGET('account_type');
		}

		$account_model->addCondition('dealer_id',$dealer_id);
		if($_GET['filter']){
			$account_model->addCondition('created_at','>=',$from_date);
			$account_model->addCondition('created_at','<=',$this->api->nextDate($to_date));
			
			if(isset($account_type_value)){
			$account_model->addCondition('SchemeType',$account_type_value);
			}

			if($_GET['status']){
				$status = $this->api->stickyGET('status');
				$account_model->addCondition('ActiveStatus',$status);
			}else
				$account_model->addCondition('ActiveStatus','<>',1);


			$member_join = $account_model->leftJoin('members','member_id');
			$member_join->addField('FatherName')->caption('Father/Husband Name');
			$member_join->addField('CurrentAddress');
			$member_join->addField('PhoneNos');
		}
		

		$grid->setModel($account_model,array('AccountNumber','scheme','name','FatherName','CurrentAddress','PhoneNos','dealer_id','ActiveStatus'));

		if($form->isSubmitted()){
			$grid->js()->reload(array('dealer'=>$form['dealer'],'status'=>$form['status'],'to_date'=>$form['to_date']?:0,'from_date'=>$form['from_date']?:0,'account_type'=>$form['account_type'],'filter'=>1))->execute();
		}

	}

}
