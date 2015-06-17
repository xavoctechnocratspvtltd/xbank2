<?php

class page_reports_loan_forclose extends Page {
	public $title="For Close Repots";
	function page_index(){
		// parent::init();

		$till_date="";
		
		if($_GET['to_date']){
			$till_date=$_GET['to_date'];
		}

		$form=$this->add('Form');
		$dealer=$form->addField('autocomplete/Basic','account_no');

		$form->addSubmit('GET List');

		$grid=$this->add('Grid');
		$grid->add('H3',null,'grid_buttons')->set('For Close '. date('d-M-Y',strtotime($till_date))); 
		$data = array(
 					array( 's_no' => '1', 
 							'account_no' => 'Smith', 
 							'member_name'=>'2000', 
 							'loan_amount'=>'1000', 
 							'monthly_interest'=>'12-09-100',
 							'panality'=>'any',
 							'other_charge'=>'any',
 							'for_close_charge'=>'1000', 
 							'time_over_charge'=>'1000', 
 							'total_amount_deposited'=>'1000', 
 							'for_close_amount'=>'1000', 
 						)
				);

		// $account_model->add('Controller_Acl');
		$grid->addColumn('s_no');
		$grid->addColumn('account_no');
		$grid->addColumn('member_name');
		$grid->addColumn('loan_amount');
		$grid->addColumn('monthly_interest');
		$grid->addColumn('panality');
		$grid->addColumn('other_charge');
		$grid->addColumn('for_close_charge');
		$grid->addColumn('time_over_charge');
		$grid->addColumn('total_amount_deposited');
		$grid->addColumn('for_close_amount');

		$grid->setSource($data);

		if($form->isSubmitted()){
			$grid->js()->reload(array('dealer'=>$form['dealer'],'agent'=>$form['agent'],'to_date'=>$form['to_date']?:0,'from_date'=>$form['from_date']?:0,'filter'=>1))->execute();
		}	

	}

}
