<?php

class page_schemes_SavingAndCurrent extends Page{
	function init(){
		parent::init();

		$crud=$this->add('xCRUD');
		$scheme_SavingAndCurrent_model =$this->add('Model_Scheme_SavingAndCurrent');
		
		$crud->addHook('myupdate',function($crud,$form){
			if($crud->isEditing('edit')) return false;
						
			$SavingAndCurrent_scheme_model = $crud->add('Model_Scheme_SavingAndCurrent');
			$SavingAndCurrent_scheme_model->createNewScheme($form['name'],$form['balance_sheet_id'], ACCOUNT_TYPE_BANK, ACCOUNT_TYPE_BANK, $is_SavingAndCurrentType=true, $other_values=$form->getAllFields(),$form,$form->api->now);
			return true;
		});

		if($crud->isEditing("add")){
		    $o=$crud->form->add('Order');
		}

		if($crud->isEditing('edit')){
			$scheme_SavingAndCurrent_model->hook('editing');
		}

		$crud->setModel($scheme_SavingAndCurrent_model,array('name','MinLimit','MaxLimit','Interest','ActiveStatus','balance_sheet_id','SchemePoints','SchemeGroup','isDepriciable','DepriciationPercentBeforeSep','DepriciationPercentAfterSep'));

		
		if($crud->grid){
			$crud->grid->addPaginator(10);
		}

		if($crud->isEditing('add')){
			$o->now();
		}

		
	}
}