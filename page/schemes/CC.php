<?php

class page_schemes_CC extends Page{
	function init(){
		parent::init();

		$crud=$this->add('xCRUD');
		$scheme_cc_model =$this->add('Model_Scheme_CC');
		
		$crud->addHook('myupdate',function($crud,$form){
			if($crud->isEditing('edit')) return false;
						
			$cc_scheme_model = $crud->add('Model_Scheme_CC');
			$cc_scheme_model->createNewScheme($form['name'],$form['balance_sheet_id'], ACCOUNT_TYPE_CC, ACCOUNT_TYPE_CC, $is_loanType=ACCOUNT_TYPE_CC, $other_values=$form->getAllFields(),$form,$form->api->now);
			return true;
		});

		if($crud->isEditing("add")){
		    $o=$crud->form->add('Order');
		}

		if($crud->isEditing('edit')){
			$scheme_cc_model->hook('editing');
		}

		$crud->setModel($scheme_cc_model,array('name','MinLimit','MaxLimit','Interest','AccountOpenningCommission','ActiveStatus','balance_sheet_id','ProcessingFeesinPercent','ProcessingFees','SchemePoints','SchemeGroup'));

		
		if($crud->grid){
			$crud->grid->addPaginator(10);
		}

		if($crud->isEditing('add')){
			$o->now();
		}

		
	}
}