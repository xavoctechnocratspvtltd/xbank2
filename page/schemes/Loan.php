<?php

class page_schemes_Loan extends Page{
	function init(){
		parent::init();

		$crud=$this->add('xCRUD');
		$scheme_Loan_model =$this->add('Model_Scheme_Loan');
		
		$crud->addHook('myupdate',function($crud,$form){
			if($crud->isEditing('edit')) return false;
						
			$Loan_scheme_model = $crud->add('Model_Scheme_Loan');
			$Loan_scheme_model->createNewScheme($form['name'],$form['balance_sheet_id'], ACCOUNT_TYPE_LOAN, ACCOUNT_TYPE_LOAN, $is_loanType=$form['loan_type'], $other_values=$form->getAllFields(),$form,$form->api->now);
			return true;
		});

		if($crud->isEditing("add")){
		    $o=$crud->form->add('Order');
		    $crud->form->addField('DropDown','loan_type')->setEmptyText('Please select')->setValueList(array('VL'=>'VL','PL'=>'PL','SL'=>'SL'))->validateNotNull();
		}

		if($crud->isEditing('edit')){
			$scheme_Loan_model->hook('editing');
		}

		$crud->setModel($scheme_Loan_model,array('name','MinLimit','MaxLimit','Interest','ReducingOrFlatRate','PremiumMode','NumberOfPremiums','ActiveStatus','balance_sheet_id','ProcessingFeesinPercent','ProcessingFees','SchemePoints','SchemeGroup'));

		
		if($crud->grid){
			$crud->grid->addPaginator(10);
		}

		if($crud->isEditing('add')){
			$o->now();
		}

		
	}
}