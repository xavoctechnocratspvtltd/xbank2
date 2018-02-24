<?php

class page_reports_general_fixedassets extends Page {
	public $title="Fixed Assets Repots";
	function page_index(){
		// parent::init();

		$till_date="";
		$fix_assets_type="";
		
		if($_GET['from_date']){
			$from_date = $this->api->stickyGET('from_date');
		}else{
			$from_date = $this->api->getFinancialYear($this->app->now,'start');
		}

		if($_GET['to_date']){
			$to_date = $this->api->stickyGET('to_date');			
		}else{
			$to_date = $this->api->getFinancialYear($this->app->now,'end');
		}

		if($_GET['fix_assets_type']){
			$fix_assets_type = $this->api->stickyGET('fix_assets_type');
		}

		$form=$this->add('Form');
		$assets_type_field=$form->addField('DropDown','fixed_assets_type');
		$assets_type_field->setValueList(array('Fixed Assets'=>'Fixed Assets','plant & machionary'=>'plant & machionary','computer & printer'=>'computer & printer','furniture & fix'=>'furniture & fix','All'=>'All'));

		$form->addField('DatePicker','from_date');
		$form->addField('DatePicker','to_date');
		$form->addSubmit('GET List');

		$grid=$this->add('Grid_Report_FixedAssets',array('from_date'=>$from_date,'to_date' => $to_date,'fixed_assets_type'=>$fix_assets_type));

		$account_model = $this->add('Model_Account')->addCondition('ActiveStatus',true);
		
		$scheme_id = -1;
		if($_GET['filter']){
			$this->api->stickyGET('filter');
			
			$grid->add('H3',null,'grid_buttons')->set('Fixed Assets As On '. date('d-M-Y',strtotime($till_date)));
			
			if($_GET['scheme_id']){
				$scheme_id = $this->api->stickyGET('scheme_id');
			}
		}
		if($_GET['fix_assets_type']!='All'){
			$account_model->addCondition('scheme_id',$scheme_id);
		}else{
			$account_model->addCondition('scheme_name',array('Fixed Assets','plant & machionary','computer & printer','furniture & fix'));

		}

		$financial_year = $this->api->getFinancialYear();
		
		$account_model->addExpression('created_at')->set(function($m,$q){
			return $m->add('Model_Transaction')->getField('created_at');
		});	
		//Purchase Date: First Tranaction Date in the current financial year
		// $account_model->addExpression('purchase_date')->set(function($m,$q)use($financial_year){
		// 	$tra_model = $m->add('Model_Transaction');
		// 	$tra_model->addCondition('reference_id',$m->id);
		// 	$tra_model->addCondition('created_at','>=',$financial_year['start_date']);
		// 	$tra_model->setLimit(1);
		// 	return $tra_model->fieldQuery('created_at');

		// 	// return $m->refSQL('RelatedTransactions')->addCondition('created_at','>=',$financial_year['start_date'])->addCondition('created_at','<=',$financial_year['end_date'])->setLimit(1)->fieldQuery('created_at');
		// });

		//Uner Head: SchemeType		
		//Closing Balance : DR Balance
		$account_model->add('Controller_Acl');
		$grid->setModel($account_model,array('AccountNumber','created_at','scheme_name'));
		if($form->isSubmitted()){
			$scheme = $this->add('Model_Scheme');
			$scheme->addCondition('SchemeGroup',$form['fixed_assets_type']);
			$scheme->tryLoadAny();

			$grid->js()->reload(array('scheme_id'=>$scheme->id,'to_date'=>$form['to_date']?:0,'from_date'=>$form['from_date']?:0,'filter'=>1,'fix_assets_type'=>$form['fixed_assets_type']))->execute();
		}	

	}

}
