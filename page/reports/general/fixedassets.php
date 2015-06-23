<?php

class page_reports_general_fixedassets extends Page {
	public $title="Fixed Assets Repots";
	function page_index(){
		// parent::init();

		$till_date="";
		
		if($_GET['till_date']){
			$till_date=$_GET['till_date'];
		}

		$form=$this->add('Form');
		$assets_type_field=$form->addField('autocomplete/Basic','fixed_assets_type');
		$scheme_model = $this->add('Model_Scheme')->addCondition('SchemeGroup','Fixed Assets');
		$assets_type_field->setModel($scheme_model);

		// $assets_type_field->setModel();
		$form->addField('DatePicker','as_on_date');
		$form->addSubmit('GET List');

		$grid=$this->add('Grid_Report_FixedAssets',array('till_date' => $till_date));
		$grid->add('H3',null,'grid_buttons')->set('Fixed Assets As On '. date('d-M-Y',strtotime($till_date)));

		$account_model = $this->add('Model_Account');
		$scheme_id = -1;
		if($_GET['filter']){
			if($_GET['scheme_id']){
				$scheme_id = $this->api->stickyGET('scheme_id');
			}
		}
		$account_model->addCondition('scheme_id',$scheme_id);

		$grid->setModel($account_model,array('AccountNumber',''));

		if($form->isSubmitted()){
			$grid->js()->reload(array('scheme_id'=>$form['fixed_assets_type'],'till_date'=>$form['as_on_date']?:0,'filter'=>1))->execute();
		}	

	}

}
