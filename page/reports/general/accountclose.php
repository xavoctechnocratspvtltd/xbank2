<?php

class page_reports_general_accountclose extends Page {
	public $title="Account Close Repots";
	function page_index(){
		// parent::init();

		$till_date="";
		
		if($_GET['to_date']){
			$till_date=$_GET['to_date'];
		}

		$form=$this->add('Form');
		$form->addField('DatePicker','from_date');
		$form->addField('DatePicker','to_date');
		$account_type=$form->addField('autocomplete/Basic','account_type');
		$form->addSubmit('GET List');

		$grid=$this->add('Grid');
		$grid->add('H3',null,'grid_buttons')->set('Account Close Report As On '. date('d-M-Y',strtotime($till_date))); 
		$data = array(
 					array( 's_no' => '1', 'member_id' => 'Smith', 'account_no'=>'12-09-100','scheme'=>'2000', 'member_name'=>'any','father_name'=>'any','address'=>'1000','phone_no' )
				);

		// $account_model->add('Controller_Acl');
		$grid->addColumn('s_no');
		$grid->addColumn('member_id');
		$grid->addColumn('account_no');
		$grid->addColumn('scheme');
		$grid->addColumn('member_name');
		$grid->addColumn('father_name');
		$grid->addColumn('address');
		$grid->addColumn('phone_no');

		$grid->setSource($data);
		
		if($form->isSubmitted()){
			$grid->js()->reload(array('dealer'=>$form['dealer'],'agent'=>$form['agent'],'to_date'=>$form['to_date']?:0,'from_date'=>$form['from_date']?:0,'filter'=>1))->execute();
		}	

	}

}
