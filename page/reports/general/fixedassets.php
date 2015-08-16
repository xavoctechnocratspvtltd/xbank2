<?php

class page_reports_general_fixedassets extends Page {
	public $title="Fixed Assets Repots";
	function page_index(){
		// parent::init();

		$till_date="";
		$fix_assets_type="";
		if($_GET['till_date']){
			$till_date = $this->api->stickyGET('till_date');			
		}

		if($_GET['fix_assets_type']){
			$fix_assets_type = $this->api->stickyGET('fix_assets_type');
		}

		$form=$this->add('Form');
		$assets_type_field=$form->addField('DropDown','fixed_assets_type');
		$assets_type_field->setValueList(array('Fixed Assets'=>'Fixed Assets','plant & machionary'=>'plant & machionary','computer & printer'=>'computer & printer','furniture & fix'=>'furniture & fix'));

		$form->addField('DatePicker','as_on_date');
		$form->addSubmit('GET List');

		$grid=$this->add('Grid_Report_FixedAssets',array('till_date' => $till_date,'fixed_assets_type'=>$fix_assets_type));

		$account_model = $this->add('Model_Account')->addCondition('branch_id',$this->api->current_branch['id']);
		
		$scheme_id = -1;
		if($_GET['filter']){
			$this->api->stickyGET('filter');
			
			$grid->add('H3',null,'grid_buttons')->set('Fixed Assets As On '. date('d-M-Y',strtotime($till_date)));
			
			if($_GET['scheme_id']){
				$scheme_id = $this->api->stickyGET('scheme_id');
			}
		}
		$account_model->addCondition('scheme_id',$scheme_id);

		$financial_year = $this->api->getFinancialYear();

		//Purchase Date: First Tranaction Date in the current financial year
		$account_model->addExpression('purchase_date')->set(function($m,$q)use($financial_year){
			$tra_model = $m->add('Model_Transaction');
			$tra_model->addCondition('reference_id',$m->id);
			$tra_model->addCondition('created_at','>=',$financial_year['start_date']);
			$tra_model->setLimit(1);
			return $tra_model->fieldQuery('created_at');

			// return $m->refSQL('RelatedTransactions')->addCondition('created_at','>=',$financial_year['start_date'])->addCondition('created_at','<=',$financial_year['end_date'])->setLimit(1)->fieldQuery('created_at');
		});

		//Uner Head: SchemeType		
		//Closing Balance : DR Balance

		$grid->setModel($account_model,array('AccountNumber','purchase_date','SchemeType'));

		if($form->isSubmitted()){
			$scheme = $this->add('Model_Scheme');
			$scheme->addCondition('SchemeGroup',$form['fixed_assets_type']);
			$scheme->tryLoadAny();

			$grid->js()->reload(array('scheme_id'=>$scheme->id,'till_date'=>$form['as_on_date']?:0,'filter'=>1,'fix_assets_type'=>$form['fixed_assets_type']))->execute();
		}	

	}

}