<?php

class page_reports_deposit_tdsquaterly extends Page {
	public $title="TDS Quaterly Reports";
	function page_index(){
		// parent::init();

		$till_date="";
		
		if($_GET['to_date']){
			$till_date=$_GET['to_date'];
		}

		$form=$this->add('Form');
		$dealer=$form->addField('autocomplete/Basic','qtr');

		$form->addSubmit('GET List');

		$grid=$this->add('Grid');
		$grid->add('H3',null,'grid_buttons')->set('For Close '. date('d-M-Y',strtotime($till_date))); 
		$data = array(
 					array( 's_no' => '1', 
 							'name_and_address' => 'Smith', 
 							'pan_no'=>'2000', 
 							'month'=>'1000', 
 							'tds_percent'=>'12-09-100',
 							'total_comm'=>'any',
 							'tds_amt'=>'any',
 							'date_of_tds'=>'1000', 
 							'ch_no'=>'1000', 
 							'net_amount'=>'1000', 
 						)
				);

		// $account_model->add('Controller_Acl');
		$grid->addColumn('s_no');
		$grid->addColumn('name_and_address');
		$grid->addColumn('pan_no');
		$grid->addColumn('month');
		$grid->addColumn('tds_percent');
		$grid->addColumn('total_comm');
		$grid->addColumn('tds_amt');
		$grid->addColumn('date_of_tds');
		$grid->addColumn('ch_no');
		$grid->addColumn('net_amount');

		$grid->setSource($data);

		if($form->isSubmitted()){
			$grid->js()->reload(array('dealer'=>$form['dealer'],'agent'=>$form['agent'],'to_date'=>$form['to_date']?:0,'from_date'=>$form['from_date']?:0,'filter'=>1))->execute();
		}	

	}

}
