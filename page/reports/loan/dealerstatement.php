<?php

class page_reports_loan_dealerstatement extends Page {
	public $title="Dealer Statement Repots";
	function page_index(){
		// parent::init();

		$till_date="";
		
		if($_GET['to_date']){
			$till_date=$_GET['to_date'];
		}

		$form=$this->add('Form');
		$dealer=$form->addField('autocomplete/Basic','dealer');
		$form->addField('DatePicker','from_date');
		$form->addField('DatePicker','to_date');
		$form->addField('autocomplete/Basic','account_type');
		$form->addField('DropDown','status')->setValueList(array('active'=>'Active','inactive'=>'Inactive'));

		$form->addSubmit('GET List');

		$grid=$this->add('Grid');
		$grid->add('H3',null,'grid_buttons')->set('Dealer Payment Detail As On '. date('d-M-Y',strtotime($till_date))); 
		$data = array(
 					array( 's_no' => '1', 
 							'account_no' => 'Smith', 
 							'scheme'=>'12-09-100',
 							'member_name'=>'2000', 
 							'father_name'=>'any',
 							'current_address'=>'any',
 							'phone_no'=>'1000', 
 							'loan_amount'=>'1000', 
 							'file_charge'=>'1000', 
 							'net_amount'=>'1000', 
 							'bank_detail'=>'1000', 
 						)
				);

		// $account_model->add('Controller_Acl');
		$grid->addColumn('s_no');
		$grid->addColumn('account_no');
		$grid->addColumn('scheme');
		$grid->addColumn('member_name');
		$grid->addColumn('father_name');
		$grid->addColumn('current_address');
		$grid->addColumn('phone_no');
		$grid->addColumn('loan_amount');
		$grid->addColumn('net_amount');
		$grid->addColumn('file_charge');
		$grid->addColumn('bank_detail');

		$grid->setSource($data);

		if($form->isSubmitted()){
			$grid->js()->reload(array('dealer'=>$form['dealer'],'agent'=>$form['agent'],'to_date'=>$form['to_date']?:0,'from_date'=>$form['from_date']?:0,'filter'=>1))->execute();
		}	

	}

}
