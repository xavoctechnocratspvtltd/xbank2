<?php

class page_reports_general_closingbalanceofaccount extends Page {
	public $title="Closing Balance of Account Repots";
	function page_index(){
		// parent::init();

		$till_date="";
		
		$selected_account_type = $this->api->stickyGET('account_type');
		$filter = $this->api->stickyGET('filter');

		if($_GET['as_on_date']){
			$till_date=$this->api->nextDate($_GET['as_on_date']);
		}

		$form=$this->add('Form');
		$form->addField('DatePicker','as_on_date');
		$account_type=$form->addField('DropDown','account_type');
		$array_value = $array_key = explode(',', ACCOUNT_TYPES);
		$account_type->setValueList(array_combine($array_key, $array_value))->setEmptyText('Select Account type');

		$form->addSubmit('GET List');

		$grid=$this->add('Grid_Report_ClosingBalanceOfAccount',array('as_on_date'=>$till_date));
		$grid->add('H3',null,'grid_buttons')->set('Account Close Report As On '. date('d-M-Y',strtotime($till_date)));

		$account_model = $this->add('Model_Account');
		$member_join = $account_model->leftJoin('members','member_id');
		$member_join->addField('FatherName')->caption('Father/Husband Name');
		$member_join->addField('PermanentAddress');
		$member_join->addField('PhoneNos');
		
		if($filter){
			if($selected_account_type){
				$account_model->addCondition('SchemeType',$selected_account_type);
			}
			// if($_GET['as_on_date'])
				// $account_model->addCondition('created_at','<',$this->api->nextDate($_GET['as_on_date']));

		}
	
		$grid->setModel($account_model,array('AccountNumber','member','FatherName','PermanentAddress','PhoneNos','SchemeType'));
		
		if($form->isSubmitted()){
			$grid->js()->reload(array('as_on_date'=>$form['as_on_date']?:0,'account_type'=>$form['account_type'],'filter'=>1))->execute();
		}

	}

}
