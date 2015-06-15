<?php

class page_reports_general_memberdepositeandloan extends Page {
	public $title="Member Deposite and Loan Repots";
	function page_index(){
		// parent::init();

		$till_date="";
		
		if($_GET['to_date']){
			$till_date=$_GET['to_date'];
		}

		$form=$this->add('Form');
		$form->addField('DatePicker','as_on_date');
		$account_type=$form->addField('DropDown','status')->setValueList(array('active'=>'Active','inactive'=>'Inactive'));
		$form->addSubmit('GET List');

		$grid=$this->add('Grid');
		$grid->add('H3',null,'grid_buttons')->set('Member Deposite and Loan Report As On '. date('d-M-Y',strtotime($till_date))); 
		$data = array(
 					array( 's_no' => '1', 
 							'member_name'=>'12-09-100',
 							'father_husband_name'=>'2000', 
 							'sm_no'=>'',
 							'share_account_amount'=>'any',
 							'add_fees'=>'any',
 							'deposite_amount'=>'1000',
 							'loan_amount'=>'1000',
 							'purpose_for_loan'=>'1000',
 						)
				);

		// $account_model->add('Controller_Acl');
		$grid->addColumn('s_no');
		$grid->addColumn('member_name');
		$grid->addColumn('father_husband_name');
		$grid->addColumn('sm_no');
		$grid->addColumn('share_account_amount');
		$grid->addColumn('add_fees');
		$grid->addColumn('deposite_amount');
		$grid->addColumn('loan_amount');
		$grid->addColumn('purpose_for_loan');

		$grid->setSource($data);
		
		if($form->isSubmitted()){
			$grid->js()->reload(array('dealer'=>$form['dealer'],'agent'=>$form['agent'],'to_date'=>$form['to_date']?:0,'from_date'=>$form['from_date']?:0,'filter'=>1))->execute();
		}	

	}

}
