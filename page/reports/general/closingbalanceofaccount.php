<?php

class page_reports_general_closingbalanceofaccount extends Page {
	public $title="Closing Balance of Account Repots";
	function page_index(){
		// parent::init();

		$till_date="";
		
		if($_GET['as_on_date']){
			$till_date=$this->api->nextDate($_GET['as_on_date']);
		}

		$form=$this->add('Form');
		$form->addField('DatePicker','as_on_date');
		$account_type=$form->addField('DropDown','account_type');
		$account_type->setValueList(explode(',', ACCOUNT_TYPES))->setEmptyText('Select Account type');

		$form->addSubmit('GET List');

		$grid=$this->add('Grid_Report_ClosingBalanceOfAccount',array('as_on_date'=>$till_date));
		$grid->add('H3',null,'grid_buttons')->set('Account Close Report As On '. date('d-M-Y',strtotime($till_date)));
		
		$account_model = $this->add('Model_Account');
		$member_join = $account_model->leftJoin('members','member_id');
		$member_join->addField('FatherName')->caption('Father/Husband Name');
		$member_join->addField('PermanentAddress');
		$member_join->addField('PhoneNos');

		if($_GET['filter']){
			if($_GET['account_type']){
				$account_model->addCondition('SchemeType',$_GET['account_type']);
			}
		}
	
		$grid->setModel($account_model,array('AccountNumber','member','FatherName','PermanentAddress','PhoneNos','SchemeType'));
		
		if($form->isSubmitted()){
			throw new \Exception($form['account_type']);
			$grid->js()->reload(array('as_on_date'=>$form['as_on_date']?:0,'account_type'=>$form['account_type'],'filter'=>1))->execute();
		}

	}

}
