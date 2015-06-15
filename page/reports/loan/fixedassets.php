<?php

class page_reports_general_fixedassets extends Page {
	public $title="Fixed Assets Repots";
	function page_index(){
		// parent::init();

		$till_date="";
		
		if($_GET['to_date']){
			$till_date=$_GET['to_date'];
		}

		$form=$this->add('Form');
		$assets_type_field=$form->addField('autocomplete/Basic','fixed_assets_type');
		$form->addField('DatePicker','as_on_date');
		$form->addSubmit('GET List');

		$grid=$this->add('Grid');
		$grid->add('H3',null,'grid_buttons')->set('Fixed Assets As On '. date('d-M-Y',strtotime($till_date))); 
		$data = array(
 					array( 's_no' => '1', 'item_name' => 'Smith', 'purchase_date'=>'12-09-100','opening_amount'=>'2000', 'under_head'=>'any','depretiation_at'=>'any','closing_balance'=>'1000' )
				);

		// $account_model->add('Controller_Acl');
		$grid->addColumn('s_no');
		$grid->addColumn('item_name');
		$grid->addColumn('purchase_date');
		$grid->addColumn('opening_amount');
		$grid->addColumn('under_head');
		$grid->addColumn('depretiation_at');
		$grid->addColumn('closing_balance');

		$grid->setSource($data);

		if($form->isSubmitted()){
			$grid->js()->reload(array('dealer'=>$form['dealer'],'agent'=>$form['agent'],'to_date'=>$form['to_date']?:0,'from_date'=>$form['from_date']?:0,'filter'=>1))->execute();
		}	

	}

}
