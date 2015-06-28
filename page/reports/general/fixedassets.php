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
		$assets_type_field=$form->addField('DropDown','fixed_assets_type');
		$assets_type_field->setValueList(array('Fixed Assets'=>'Fixed Assets','plant & machionary'=>'plant & machionary','computer & printer'=>'computer & printer','furniture & fix'=>'furniture & fix'));

		$form->addField('DatePicker','as_on_date');
		$form->addSubmit('GET List');

		$grid=$this->add('Grid_Report_FixedAssets',array('till_date' => $till_date));
		$grid->add('H3',null,'grid_buttons')->set('Fixed Assets As On '. date('d-M-Y',strtotime($till_date)));

		$account_model = $this->add('Model_Account')->addCondition('branch_id',$this->api->current_branch['id']);
		
		$scheme_id = -1;
		if($_GET['filter']){
			if($_GET['scheme_id']){
				$scheme_id = $this->api->stickyGET('scheme_id');
			}
		}
		$account_model->addCondition('scheme_id',$scheme_id);


		//Purchase Date: First Tranaction Date in the current financial year
		//Opening Amount: Current Financial Year ka opening Amount no according to as on date
		//Uner Head: SchemeType
		//Depretitaion at: Plane&Machinary: 15%, Computer and Printer: 60% , Funrinture and fix:10%, Fixed Assets: 10%;
		//Closing Balance : DR Balance

		$grid->setModel($account_model,array('AccountNumber',''));

		if($form->isSubmitted()){
			$scheme = $this->add('Model_Scheme');
			$scheme->addCondition('SchemeGroup',$form['fixed_assets_type']);
			$scheme->tryLoadAny();

			$grid->js()->reload(array('scheme_id'=>$scheme->id,'till_date'=>$form['as_on_date']?:0,'filter'=>1))->execute();
		}	

	}

}
