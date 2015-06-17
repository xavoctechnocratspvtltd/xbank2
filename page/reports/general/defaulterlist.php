<?php

class page_reports_general_defaulterlist extends Page {
	public $title="Defaulter List Repots";
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
		$grid->add('H3',null,'grid_buttons')->set('Defaulter List As On '. date('d-M-Y',strtotime($till_date))); 
		$data = array(
 					array( 'not_known' => '1')
				);

		// $account_model->add('Controller_Acl');
		$grid->addColumn('not_known');

		$grid->setSource($data);

		if($form->isSubmitted()){
			$grid->js()->reload(array('dealer'=>$form['dealer'],'agent'=>$form['agent'],'to_date'=>$form['to_date']?:0,'from_date'=>$form['from_date']?:0,'filter'=>1))->execute();
		}	

	}

}
