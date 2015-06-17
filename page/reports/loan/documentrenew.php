<?php

class page_reports_loan_documentrenew extends Page {
	public $title="CC Document Renew Repots";
	function page_index(){
		// parent::init();

		$till_date="";
		
		if($_GET['to_date']){
			$till_date=$_GET['to_date'];
		}

		$form=$this->add('Form');
		$dealer=$form->addField('autocomplete/Basic','account_no');
		$dealer=$form->addField('DatePicker','as_on_date');

		$form->addSubmit('GET List');

		$grid=$this->add('Grid');
		$grid->add('H3',null,'grid_buttons')->set('For Close '. date('d-M-Y',strtotime($till_date))); 
		$data = array(
 					array( 's_no' => '1',
 							'account_no' => 'Smith', 
 							'cc_name'=>'1000', 
 							'member_name'=>'2000', 
 							'father_name'=>'12-09-100',
 							'current_address'=>'any',
 							'contact_no'=>'1000', 
 							'cc_limit'=>'1000', 
 							'current_balance'=>'1000', 
 							'last_document_renew_date'=>'1000',
 						)
				);

		// $account_model->add('Controller_Acl');
		$grid->addColumn('s_no');
		$grid->addColumn('account_no');
		$grid->addColumn('cc_name');
		$grid->addColumn('member_name');
		$grid->addColumn('father_name');
		$grid->addColumn('current_address');
		$grid->addColumn('contact_no');
		$grid->addColumn('cc_limit');
		$grid->addColumn('current_balance');
		$grid->addColumn('last_document_renew_date');

		$grid->setSource($data);

		if($form->isSubmitted()){
			$grid->js()->reload(array('dealer'=>$form['dealer'],'agent'=>$form['agent'],'to_date'=>$form['to_date']?:0,'from_date'=>$form['from_date']?:0,'filter'=>1))->execute();
		}	

	}

}
