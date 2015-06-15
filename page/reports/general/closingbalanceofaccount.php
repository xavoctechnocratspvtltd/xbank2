<?php

class page_reports_general_closingbalanceofaccount extends Page {
	public $title="Closing Balance of Account Repots";
	function page_index(){
		// parent::init();

		$till_date="";
		
		if($_GET['to_date']){
			$till_date=$_GET['to_date'];
		}

		$form=$this->add('Form');
		$form->addField('DatePicker','as_on_date');
		$account_type=$form->addField('autocomplete/Basic','account_type');
		$form->addSubmit('GET List');

		$grid=$this->add('Grid');
		$grid->add('H3',null,'grid_buttons')->set('Account Close Report As On '. date('d-M-Y',strtotime($till_date))); 
		$data = array(
 					array( 's_no' => '1', 
 							'account_no' => 'Smith', 
 							'member_name'=>'12-09-100',
 							'father_husband_name'=>'2000', 
 							'address'=>'any',
 							'phone_no'=>'any',
 							'closing_balance_of_account'=>'1000',
 							'sm_account_no'=>'' 
 						)
				);

		// $account_model->add('Controller_Acl');
		$grid->addColumn('s_no');
		$grid->addColumn('account_no');
		$grid->addColumn('member_name');
		$grid->addColumn('father_husband_name');
		$grid->addColumn('address');
		$grid->addColumn('phone_no');
		$grid->addColumn('closing_balance_of_account');
		$grid->addColumn('sm_account_no');

		$grid->setSource($data);
		
		if($form->isSubmitted()){
			$grid->js()->reload(array('dealer'=>$form['dealer'],'agent'=>$form['agent'],'to_date'=>$form['to_date']?:0,'from_date'=>$form['from_date']?:0,'filter'=>1))->execute();
		}	

	}

}
